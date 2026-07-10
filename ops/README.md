# Lozand VPS Deployment Ops

This directory contains non-secret deployment helpers. Do not put real server
addresses, passwords, tokens, domain names, or generated production credentials
in these files.

## Files

- `hestia-lozand-provision.sh` provisions a fresh Debian/Ubuntu VPS with HestiaCP
  and deploys Lozand from Git.
- `deployment.env.example` documents the required environment variables using
  placeholders only.

## Safety Rules

- Run the script only on a fresh VPS.
- Keep production secrets in the ignored local note, a password manager, or the
  server environment. Never commit them.
- Push application code through Git. The server should clone from the production
  branch instead of using the in-app updater.
- Keep `INSTALLER_ENABLED`, `LOZAND_UTILITY_ROUTES_ENABLED`,
  `LOZAND_REMOTE_UPDATES_ENABLED`, and `LOZAND_REMOTE_PATCHES_ENABLED` disabled
  for production.

## High-Level Flow

1. Export variables from a private shell session or a temporary, ignored env
   file.
2. Copy `hestia-lozand-provision.sh` to the fresh VPS.
3. Run it as `root`.
4. Store any generated Hestia, database, mail, and app admin credentials only in
   the ignored local access note.
5. Point DNS records at the VPS, then issue SSL certificates from HestiaCP.

