# Oekomaile

## Requirements

- [Docker](https://docs.docker.com/)
- [DDEV](https://docs.ddev.com/)
- [Composer](https://getcomposer.org/)


## Local setup

The following commands are required to set up the project initially:

```bash
# install the project
cp .env.dist .env
ddev composer install
ddev start
# import database dump
vendor/bin/dep db:dump <environment>
ddev import-db --file=<environment>-<date>.sql
ddev wp search-replace 'oekomaile.de' 'oekomaile.ddev.site' --all-tables
```


## Local usage

The administration login is available on https://oekomaile.ddev.site/wp/wp-admin

To run the project use following command:

```bash
ddev start
```

To stop the project use following command:

```bash
ddev poweroff
```


## Development

### Structure

The project uses [Bedrock](https://roots.io/bedrock/docs/composer/) to manage WordPress dependencies.
The dependencies are downloaded by Composer on [WPackagist](https://wpackagist.org/), a mirror of the WordPress store.

WordPress Development can be done in following folders:

- `web/app/plugins`
- `web/app/mu-plugins`
- `web/app/themes`

WordPress instance configuration can be done in following files:

- `config/application.php` (general)
- `config/environments/*.php` (environment specific)


### Add dependency

Add a _free_ dependency (e.g. plugin or theme):

- Find corresponding package name on [WPackagist](https://wpackagist.org/)
- Add to composer via require (e.g. `composer require wpackagist-plugin/akismet`)

Add a _paid_ dependency which is not visible on WPackagist or WordPress store

- Download paid dependency from designated source
- Add them to `web/app/plugins` or `web/app/themes` folder
- Add an exemption entry to the .gitignore file (e.g. `web/app/plugins/.gitignore` with `!akismet/`)


### Update dependencies

Find outdated dependencies (e.g. plugin or theme):

```bash
ddev composer outdated --direct
```

Update dependencies

```bash
ddev composer require <package_name>
```


## Deployment

There is an automatic deployment pipeline on GitHub Actions.
See `/.github/workflows/deploy.yml`.

See `servers.yaml` for the available environments.
Please add your SSH public key to the designated server's authorized keys.

To start the manual deployment run:

```bash
# checkout to desired branch on local device
git checkout <environment_branch>
# re-establish repository authentication (needed after PC restart)
ssh-add
# run the deployment
vendor/bin/dep deploy <environment_name>
```

To run server commands with the WP-CLI, go to the environments deploy directory under `<deploy_directory>/current/`.
To edit server configuration files, go to the environments deploy directory under `<deploy_directory>/shared/`.
