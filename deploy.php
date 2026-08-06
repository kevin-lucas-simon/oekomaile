<?php

namespace Deployer;

require 'recipe/composer.php';

set('keep_releases', 5);
set('update_code_strategy', 'clone');

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

after('deploy:publish', 'deploy:clear_paths');
after('deploy:publish', 'deploy:clear_cache');

after('deploy:failed', 'deploy:unlock');

task('dump', function () {
    $environment = currentHost()->getAlias();
    $filename = "{$environment}-".date('Y-m-d').'.sql';

    $remotePath = '{{deploy_path}}/current/'.$filename;

    run("cd {{deploy_path}}/current && wp db export {$filename}");

    download($remotePath, __DIR__.'/'.$filename);

    run("rm {$remotePath}");

    writeln("Dump downloaded: {$filename}");
});
