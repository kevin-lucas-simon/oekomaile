<?php

namespace Deployer;

require 'recipe/composer.php';

set('keep_releases', 5);
set('update_code_strategy', 'clone');

set('db_backup_dir', '{{deploy_path}}/shared/backups');
set('db_keep_backups', 5);

set('composer_options', '--prefer-dist --no-dev --no-progress --no-interaction --optimize-autoloader');

set('shared_files', [
    '.env',
    'web/.htaccess',
]);
set('shared_dirs', [
    'web/app/ai1wm-backups',
    'web/app/aiowps_backups',
    'web/app/cache',
    'web/app/uploads',
]);
set('writable_dirs', [
    'web/app/cache',
    'web/app/uploads',
]);

set('clear_paths', [
    '.ddev',
    'deploy.php',
    'servers.yaml',
]);

desc('Clear cache');
task('deploy:clear_cache', function () {
    run('cd {{release_path}} && wp cache flush');
});

import('servers.yaml');

before('deploy:symlink', 'db:backup');

after('deploy:publish', 'deploy:clear_paths');
after('deploy:publish', 'deploy:clear_cache');

after('deploy:failed', 'deploy:unlock');

task('db:dump', function () {
    $environment = currentHost()->getAlias();
    $filename = "{$environment}-".date('Y-m-d').'.sql';

    $remotePath = '{{deploy_path}}/current/'.$filename;

    run("cd {{deploy_path}}/current && wp db export {$filename}");

    download($remotePath, __DIR__.'/'.$filename);

    run("rm {$remotePath}");

    writeln("Dump downloaded: {$filename}");
});

task('db:backup', function () {
    if (! test('[ -d {{deploy_path}}/current ]')) {
        return;
    }

    $backupDir = get('db_backup_dir');
    $keep = (int) get('db_keep_backups');

    $timestamp = date('Y-m-d_H-i-s');
    $filename = "db_backup_{$timestamp}.sql.gz";

    run("mkdir -p {$backupDir}");

    run("cd {{current_path}} && wp db export - | gzip > {$backupDir}/{$filename}");

    run("cd {$backupDir} && ls -1t db_backup_*.sql.gz 2>/dev/null | tail -n +".($keep + 1).' | xargs -r rm -f');
});
