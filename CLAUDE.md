# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

WordPress site (German, "Ökomaile") built on **Roots Bedrock** — Composer-managed WordPress with an improved folder layout, `.env`-based config, and WordPress core installed as a dependency under `web/wp`. Local development runs in **DDEV** (Docker); production deploys to a Hetzner server via **Deployer**.

## Commands

All local commands run inside DDEV. Prefix with `ddev` to execute in the web container.

```bash
ddev start            # boot the local environment (https://oekomaile.ddev.site)
ddev poweroff         # stop
ddev composer install # install PHP/WordPress dependencies
ddev wp <args>        # run WP-CLI (path is configured to web/wp via wp-cli.yml)
ddev mysql < dump.sql # import a DB dump
```

Lint (PHP, Laravel Pint — config in `pint.json`, `per` preset):

```bash
composer lint      # pint --test  (check only)
composer lint:fix  # pint         (autofix)
```

Pint **excludes** WordPress core (`web/wp`), third-party plugins (`web/app/plugins`), and bundled themes — only first-party PHP (notably the `oekomaile` theme and `config/`) is linted.

Deploy (Deployer, config in `deploy.php` + `servers.yaml`):

```bash
git checkout main                 # deploy the branch matching the target stage
ssh-add                           # load SSH key for the remote repo/host
vendor/bin/dep deploy prod        # deploy to the "prod" host
```

## Architecture

- **Bedrock layout.** Web root is `web/`. WordPress core lives in `web/wp` (installed by Composer, never edited). The `wp-content` dir is relocated to `web/app` (`CONTENT_DIR` = `/app`). Site loads via `web/index.php` → `web/wp/wp-blog-header.php`.

- **Configuration** is split by Bedrock convention:
  - `config/application.php` — base config for all environments; reads everything from `.env` and defines WordPress constants via `Roots\WPConfig\Config`. `WP_ENV` (default `production`) selects an optional per-environment override at `config/environments/{WP_ENV}.php` (not present by default).
  - `.env` (copied from `.env.dist`) — DB credentials, `WP_HOME`/`WP_SITEURL`, and auth salts. Required keys are enforced at boot. Under DDEV, `web/wp-config-ddev.php` injects container DB settings.
  - `web/wp-config.php` just requires `config/application.php`.

- **Dependencies are Composer-managed, not committed.** Free plugins/themes come from **WPackagist** (`wpackagist-plugin/*`, `wpackagist-theme/*`) and install into `web/app/{plugins,mu-plugins,themes}/` per the `installer-paths` in `composer.json`. To add a paid/non-WPackagist plugin, drop it into `web/app/plugins` and add a `!name/` exemption to the relevant `.gitignore` so it is tracked.

- **Custom code lives in the `oekomaile` theme** (`web/app/themes/oekomaile`) — a child theme of `twentytwentythree` (`Template:` header). It is the main place for site-specific development:
  - `functions.php` registers the `contributor` custom post type (slug `mitwirkende`, "Mitwirkende"), enables SVG uploads, and enqueues styles.
  - `style.css` imports `css/legacy_style.css` and defines fonts/design.
  - Text domain is `oekomaile`; the site is German (translations under `web/app/languages`).

## Deployment notes

- Only `main` deploys to `prod` (see `servers.yaml`). `deploy.php` keeps 5 releases, installs with `--no-dev`, shares `.env` + `web/.htaccess` and upload/backup/cache dirs across releases, and runs `wp cache flush` after publish.
- Do **not** rely on `.ddev`, `deploy.php`, or `servers.yaml` on the server — they are stripped via `clear_paths` during deploy.
