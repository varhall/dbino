<?php

use Ninjify\Nunjuck\Environment;

if (@!include __DIR__ . '/../vendor/autoload.php') {
    echo 'Install Nette Tester using `composer update --dev`';
    exit(1);
}

// Configure environment
Environment::setup(__DIR__);

define('SRC_DIR', realpath(TESTER_DIR . '/../src'));
define('CONFIG_DIR', TESTER_DIR . '/Config');
define('FIXTURES_DIR', TESTER_DIR . '/Fixtures/SQL');

Environment::setupRobotLoader(function($loader) {
    $loader->addDirectory(__DIR__);
    $loader->addDirectory(SRC_DIR);
    $loader->setAutoRefresh(true);
});

function dump(...$args)
{
    foreach ($args as $arg) {
        var_dump($arg);
    }
}

function dumpe(...$args)
{
    dump(...$args);
    \Tester\Assert::fail('Dump variable');
    die();
}
