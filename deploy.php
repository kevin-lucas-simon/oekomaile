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
