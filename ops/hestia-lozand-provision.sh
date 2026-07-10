#!/usr/bin/env bash
set -Eeuo pipefail

umask 077

require_root() {
  if [[ "${EUID}" -ne 0 ]]; then
    echo "Run this script as root." >&2
    exit 1
  fi
}

require_env() {
  local missing=()
  for name in "$@"; do
    if [[ -z "${!name:-}" ]]; then
      missing+=("${name}")
    fi
  done

  if (( ${#missing[@]} > 0 )); then
    echo "Missing required environment variables: ${missing[*]}" >&2
    exit 1
  fi
}

require_fresh_server() {
  if [[ -d /usr/local/hestia || -d /etc/hestia ]]; then
    echo "HestiaCP already exists on this server. Stop and inspect manually." >&2
    exit 1
  fi

  if find /home /var/www -maxdepth 4 \( -name public_html -o -path '*/web/*' \) 2>/dev/null | grep -q .; then
    echo "Existing web content was found. Stop and inspect manually." >&2
    exit 1
  fi
}

install_hestia() {
  export DEBIAN_FRONTEND=noninteractive
  apt-get update
  apt-get install -y ca-certificates curl wget git unzip sudo cron

  cd /root
  curl -fsSLo hst-install.sh https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install.sh

  bash hst-install.sh \
    --interactive no \
    --hostname "${SERVER_HOSTNAME}" \
    --email "${SITE_EMAIL}" \
    --username admin \
    --password "${HESTIA_ADMIN_PASSWORD}" \
    --apache yes \
    --phpfpm yes \
    --multiphp yes \
    --vsftpd yes \
    --proftpd no \
    --named yes \
    --mysql yes \
    --postgresql no \
    --exim yes \
    --dovecot yes \
    --sieve no \
    --clamav no \
    --spamassassin yes \
    --iptables yes \
    --fail2ban yes \
    --quota no \
    --api yes \
    --force
}

install_runtime_tools() {
  apt-get update
  if ! command -v node >/dev/null 2>&1 || ! node -e 'process.exit(Number(process.versions.node.split(".")[0]) >= 22 ? 0 : 1)' >/dev/null 2>&1; then
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
  fi
  apt-get install -y composer nodejs default-mysql-client
}

ensure_hestia_site() {
  local hbin="/usr/local/hestia/bin"

  "${hbin}/v-add-user" "${HESTIA_USER}" "${HESTIA_USER_PASSWORD}" "${SITE_EMAIL}" || true
  "${hbin}/v-add-web-domain" "${HESTIA_USER}" "${DOMAIN}" || true
  "${hbin}/v-add-web-domain-alias" "${HESTIA_USER}" "${DOMAIN}" "www.${DOMAIN}" || true
  "${hbin}/v-add-dns-domain" "${HESTIA_USER}" "${DOMAIN}" || true
  "${hbin}/v-add-mail-domain" "${HESTIA_USER}" "${DOMAIN}" || true
  "${hbin}/v-add-mail-account" "${HESTIA_USER}" "${DOMAIN}" "admin" "${MAIL_ADMIN_PASSWORD}" || true
  "${hbin}/v-add-mail-account" "${HESTIA_USER}" "${DOMAIN}" "no-reply" "${MAIL_NOREPLY_PASSWORD}" || true
  "${hbin}/v-add-database" "${HESTIA_USER}" mysql "${DB_NAME}" "${DB_USER}" "${DB_PASSWORD}" || true
}

write_laravel_env() {
  local app_dir="$1"
  local full_db_name="${HESTIA_USER}_${DB_NAME}"
  local full_db_user="${HESTIA_USER}_${DB_USER}"

  cat > "${app_dir}/.env" <<ENV
APP_NAME=Lozand
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Africa/Lagos
APP_URL=https://${DOMAIN}

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${full_db_name}
DB_USERNAME=${full_db_user}
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@${DOMAIN}"
MAIL_FROM_NAME="\${APP_NAME}"

INSTALLER_ENABLED=false
LOZAND_UTILITY_ROUTES_ENABLED=false
LOZAND_REMOTE_UPDATES_ENABLED=false
LOZAND_REMOTE_PATCHES_ENABLED=false
ENV

  chown "${HESTIA_USER}:${HESTIA_USER}" "${app_dir}/.env"
  chmod 600 "${app_dir}/.env"
}

deploy_app() {
  local domain_root="/home/${HESTIA_USER}/web/${DOMAIN}"
  local app_dir="${domain_root}/current"
  local public_link="${domain_root}/public_html"
  local full_db_name="${HESTIA_USER}_${DB_NAME}"
  local full_db_user="${HESTIA_USER}_${DB_USER}"

  if [[ -e "${app_dir}" ]]; then
    echo "Application directory already exists: ${app_dir}" >&2
    exit 1
  fi

  install_runtime_tools

  sudo -u "${HESTIA_USER}" git clone --branch "${REPO_BRANCH:-main}" --single-branch "${REPO_URL}" "${app_dir}"
  write_laravel_env "${app_dir}"

  sudo -u "${HESTIA_USER}" composer install --no-dev --optimize-autoloader --working-dir="${app_dir}"

  if [[ -f "${app_dir}/package-lock.json" ]]; then
    sudo -u "${HESTIA_USER}" bash -lc "cd '${app_dir}' && npm ci && npm run build"
  else
    sudo -u "${HESTIA_USER}" bash -lc "cd '${app_dir}' && npm install && npm run build"
  fi

  sudo -u "${HESTIA_USER}" bash -lc "cd '${app_dir}' && php artisan key:generate --force"

  if [[ -f "${app_dir}/public/install/database.sql" ]]; then
    MYSQL_PWD="${DB_PASSWORD}" mysql -u "${full_db_user}" "${full_db_name}" < "${app_dir}/public/install/database.sql"
  fi

  sudo -u "${HESTIA_USER}" bash -lc "cd '${app_dir}' && php artisan migrate --force"

  sudo -u "${HESTIA_USER}" \
    DEPLOY_ADMIN_NAME="${APP_ADMIN_NAME}" \
    DEPLOY_ADMIN_EMAIL="${APP_ADMIN_EMAIL}" \
    DEPLOY_ADMIN_PASSWORD="${APP_ADMIN_PASSWORD}" \
    php "${app_dir}/artisan" tinker --execute='
      \App\Models\Admin::query()->updateOrCreate(
        ["username" => "admin"],
        [
          "name" => getenv("DEPLOY_ADMIN_NAME") ?: "Super Admin",
          "email" => getenv("DEPLOY_ADMIN_EMAIL"),
          "password" => \Illuminate\Support\Facades\Hash::make(getenv("DEPLOY_ADMIN_PASSWORD")),
          "status" => "active",
        ]
      );
    '

  sudo -u "${HESTIA_USER}" bash -lc "cd '${app_dir}' && php artisan storage:link || true"
  sudo -u "${HESTIA_USER}" bash -lc "cd '${app_dir}' && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache"

  rm -rf "${public_link}"
  ln -s "${app_dir}/public" "${public_link}"
  chown -h "${HESTIA_USER}:${HESTIA_USER}" "${public_link}"
  chown -R "${HESTIA_USER}:${HESTIA_USER}" "${app_dir}"
  chmod -R ug+rwX "${app_dir}/storage" "${app_dir}/bootstrap/cache"

  (
    crontab -u "${HESTIA_USER}" -l 2>/dev/null | grep -v 'lozand:start-schedule' || true
    echo "* * * * * cd ${app_dir} && php artisan lozand:start-schedule >> /dev/null 2>&1"
  ) | crontab -u "${HESTIA_USER}" -
}

main() {
  require_root
  require_env \
    DOMAIN SERVER_HOSTNAME SITE_EMAIL \
    HESTIA_ADMIN_PASSWORD HESTIA_USER HESTIA_USER_PASSWORD \
    DB_NAME DB_USER DB_PASSWORD \
    MAIL_ADMIN_PASSWORD MAIL_NOREPLY_PASSWORD \
    REPO_URL APP_ADMIN_NAME APP_ADMIN_EMAIL APP_ADMIN_PASSWORD

  require_fresh_server
  install_hestia
  ensure_hestia_site
  deploy_app

  echo "Provisioning complete. Point DNS, issue SSL in HestiaCP, then run the verification checklist."
}

main "$@"
