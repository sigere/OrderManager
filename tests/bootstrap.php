<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

(new Filesystem())->remove(__DIR__.'/../var/cache/test');

$commands = [
    'doctrine:database:drop --force',
    'doctrine:database:create',
    'doctrine:schema:create',
    'doctrine:fixtures:load',
];

foreach ($commands as $command) {
    passthru(sprintf(
        'APP_ENV=%s php "%s/../bin/console" %s --no-interaction',
        $_ENV['APP_ENV'],
        __DIR__,
        $command
    ));
}
