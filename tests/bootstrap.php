<?php

use Ninjify\Nunjuck\Environment;

if (@!include __DIR__ . '/../vendor/autoload.php') {
    echo 'Install Nette Tester using `composer update --dev`';
    exit(1);
}

// Configure environment
Environment::setup(__DIR__);

define('SRC_DIR', realpath(TESTER_DIR . '/../src'));
define('CONFIG_DIR', TESTER_DIR . '/config');
define('FIXTURES_DIR', TESTER_DIR . '/fixtures/sql');

Environment::setupRobotLoader(function($loader) {
    $loader->addDirectory(ENGINE_DIR);
    $loader->addDirectory(SRC_DIR);
    $loader->setAutoRefresh(true);
});

