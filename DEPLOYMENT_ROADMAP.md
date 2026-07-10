# Lozand Deployment Roadmap

## Current Project Snapshot

- Application root: `lozand/Files`
- Stack: Laravel 12, PHP 8.2+, MySQL/MariaDB, Vite 7, Tailwind CSS 4
- Public web root: `lozand/Files/public`
- Bundled folders that should not be committed long-term: `vendor`, `node_modules`, `public/build`, `public/hot`, `storage/logs`, runtime cache files
- Local PHP and Composer were not available on this workstation PATH during inspection, so server validation must be done on the VPS.

## High-Risk Pre-Deployment Findings

This copy is modified/nulled. Do not put it on a public VPS until these are handled.

1. License checks are bypassed.
   - `public/install/index.php` has the remote license activation block commented out and marks license activation as successful anyway.
   - `app/Http/Controllers/Admin/Settings/ActivationController.php` contains an explicit "NULLED BY PLACEHOLDER DEV" block.

2. Public utility routes exist.
   - `routes/utils.php` exposes GET routes for cache clearing, forced storage link creation, and cron trigger under `/utils/*`.
   - These should be removed, protected, or IP-restricted before production.

3. The updater can overwrite application files from a remote server.
   - `app/Console/Commands/VulnerabilityPatch.php` downloads base64-encoded files from `https://lozand.com/api/v1/update/download/critical` and writes them into the app.
   - `app/Http/Controllers/Admin/Update/PrecheckController.php` has similar update-download behavior.

4. The build command can auto-commit and push to `main`.
   - `app/Console/Commands/BuildAssets.php` runs `git add .`, commits, and pushes to `origin main` when `--push` is used.
   - This should not be used in the deployment flow.

5. Environment files need cleanup.
   - `.env` and `.env.backup` must never be committed.
   - `.env.example` should not contain a real reusable `APP_KEY`; replace it with an empty placeholder.

## Recommended Git Branch Model

Use two long-lived branches:

- `main`: production branch deployed on the VPS.
- `develop`: working branch for changes, experiments, audits, and feature work.

Suggested flow:

```bash
cd lozand/Files
git init
git add .
git commit -m "Initial audited import"
git branch -M main
git checkout -b develop
```

Before pushing anywhere, confirm `.gitignore` excludes:

```gitignore
.env
.env.backup
.env.production
/vendor
/node_modules
/public/build
/public/hot
/public/storage
/storage/*.key
/storage/logs
/storage/framework/cache
/storage/framework/sessions
/storage/framework/views
```

Remote setup:

```bash
git remote add origin git@github.com:YOUR_ORG/YOUR_REPO.git
git push -u origin main
git push -u origin develop
```

Daily workflow:

```bash
git checkout develop
# make changes
git add .
git commit -m "Describe change"
git push origin develop
```

Release workflow:

```bash
git checkout main
git merge --no-ff develop
git tag v0.1.0
git push origin main --tags
```

## VPS Target Architecture

Recommended production setup:

- Ubuntu 24.04 LTS or 22.04 LTS
- Nginx
- PHP 8.3 FPM with required extensions
- MariaDB or MySQL 8
- Redis optional but recommended for cache/queue
- Supervisor for queue workers
- Cron for Laravel scheduler
- Certbot for HTTPS

Required PHP extensions to install/check:

```bash
php8.3-cli php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl
```

## Multi-Site VPS Hosting Layer

If the VPS should host Lozand plus other websites, do not deploy Lozand as if it owns the entire server. Treat the VPS as a small private hosting platform with isolated sites, separate Linux users, separate document roots, and per-domain Nginx/PHP-FPM configuration.

Recommended options:

1. Lightweight hosting panel
   - Best when you want a cPanel-like dashboard for adding websites, databases, SSL, DNS zones, and mail accounts.
   - Good candidates:
     - HestiaCP: supports Ubuntu 22.04/24.04 and Debian 11/12, manages users, web domains, DNS, databases, mail, and server services.
     - CloudPanel: modern panel for PHP, Node.js, Python, static sites, reverse proxies, and per-site SSH users.
   - Use a fresh OS before installing a panel.
   - Install the panel first, then create Lozand as one site inside the panel.

2. Manual Nginx virtual hosts
   - Best when you want maximum control and fewer moving parts.
   - Each site gets:
     - Linux user: `lozand`, `site2`, `site3`, etc.
     - Root path: `/var/www/sites/example.com/current/public`
     - Separate PHP-FPM pool where useful.
     - Separate database and database user.
     - Separate logs under `/var/log/nginx/example.com.*.log`.

3. Commercial cPanel/WHM
   - Best when you specifically want the familiar cPanel/WHM ecosystem.
   - Expect a paid license on top of VPS cost.
   - Use WHM to create one cPanel account per website or client.

Recommended default for this project:

- Use HestiaCP if you want the closest free/open cPanel-like experience with mail/DNS/web/database management.
- Use CloudPanel if you want a cleaner app-hosting dashboard and do not need full shared-hosting style mail/DNS management.
- Use manual Nginx if this VPS will mostly host apps you control and you prefer a lean, auditable setup.

Panel-first deployment shape:

```text
VPS
├── control panel
├── site: lozand.example.com
│   ├── document root: /home/lozand/web/lozand.example.com/public_html/current/public
│   ├── database: lozand_prod
│   └── cron: php artisan lozand:start-schedule
├── site: website-two.com
│   └── separate root/database/user
└── site: website-three.com
    └── separate root/database/user
```

Manual multi-site deployment shape:

```text
/var/www/sites/
├── lozand.example.com/
│   ├── current -> releases/20260709_120000
│   ├── releases/
│   └── shared/
│       ├── .env
│       └── storage
├── website-two.com/
└── website-three.com/
```

For Lozand specifically, cron should call the app's custom scheduler command, not only Laravel's default scheduler:

```cron
* * * * * cd /path/to/lozand/current && php artisan lozand:start-schedule >> /dev/null 2>&1
```

The public `/utils/cronjob` route should stay disabled in production unless HTTP cron is absolutely required. If HTTP cron is needed, protect it with a secret token or IP allowlist before enabling `LOZAND_UTILITY_ROUTES_ENABLED=true`.

## First VPS Provisioning

Create app user and directories:

```bash
sudo adduser deploy
sudo usermod -aG www-data deploy
sudo mkdir -p /var/www/lozand
sudo chown -R deploy:www-data /var/www/lozand
```

Clone production branch:

```bash
sudo -iu deploy
git clone -b main git@github.com:YOUR_ORG/YOUR_REPO.git /var/www/lozand
cd /var/www/lozand
```

Install dependencies and build assets:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Create production environment:

```bash
cp .env.example .env
php artisan key:generate
```

Then edit `.env` manually on the VPS:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lozand_prod
DB_USERNAME=lozand_user
DB_PASSWORD=strong_password_here
```

Initialize Laravel:

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Set permissions:

```bash
sudo chown -R deploy:www-data /var/www/lozand
sudo find /var/www/lozand -type f -exec chmod 0644 {} \;
sudo find /var/www/lozand -type d -exec chmod 0755 {} \;
sudo chmod -R ug+rw /var/www/lozand/storage /var/www/lozand/bootstrap/cache
```

## Nginx Site

Use `public` as the only web root:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/lozand/public;

    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ^~ /install {
        deny all;
    }

    location ^~ /utils {
        deny all;
    }
}
```

Enable HTTPS:

```bash
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

## Queue And Cron

Laravel scheduler:

```cron
* * * * * cd /var/www/lozand && php artisan schedule:run >> /dev/null 2>&1
```

Supervisor worker:

```ini
[program:lozand-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lozand/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/lozand/storage/logs/worker.log
stopwaitsecs=3600
```

## Production Deploy Command Sequence

Run this on the VPS for each production release:

```bash
cd /var/www/lozand
git fetch origin
git checkout main
git pull --ff-only origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan down
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
```

For safer zero/low downtime later, move to a release-directory approach:

```text
/var/www/lozand/releases/20260709_120000
/var/www/lozand/current -> releases/20260709_120000
/var/www/lozand/shared/.env
/var/www/lozand/shared/storage
```

## Security Hardening Before Launch

Minimum launch gate:

- Obtain a legitimate license or replace this package with a clean lawful source.
- Remove or block `/install` after installation.
- Remove or protect `/utils/*`.
- Disable admin file manager/code editor in production unless absolutely required.
- Remove or disable auto-updater endpoints until source integrity is trusted.
- Replace `.env.example` secrets with blanks/placeholders.
- Generate a fresh production `APP_KEY`.
- Rotate all payment/API credentials after audit.
- Set `APP_DEBUG=false`.
- Require HTTPS.
- Use strong admin password and OTP.
- Put database on private localhost only.
- Add server firewall: allow only SSH, HTTP, HTTPS.
- Back up database and uploaded storage daily.

## Suggested Milestones

1. Repository cleanup
   - Initialize Git in `lozand/Files`.
   - Sanitize `.env.example`.
   - Ensure generated/runtime files are ignored.
   - Commit to `main`, branch `develop`.

2. Security audit
   - Remove installer and public utility routes.
   - Review updater and file-manager behavior.
   - Run Composer audit, npm audit, and PHP static checks on a machine with PHP/Composer.

3. Staging VPS
   - Deploy `develop` to a staging subdomain.
   - Test login, admin dashboard, deposits, withdrawals, KYC, email, cron, queue, and asset rendering.

4. Production VPS
   - Deploy only `main`.
   - Configure domain, SSL, backups, monitoring, and firewall.

5. Ongoing work
   - All edits happen on `develop`.
   - Merge to `main` only after tests and staging verification.
   - Tag every production release.
