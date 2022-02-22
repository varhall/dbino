<?php

namespace Tests\Engine;

use Nette\Configurator;

trait TestContainer
{
    /** @var Nette\DI\Container */
    private $container;


    protected function getContainer()
    {
        if ($this->container === NULL) {
            $this->container = $this->createContainer();
        }

        return $this->container;
    }

    protected function createContainer()
    {
        $configurator = new Configurator();

        $configurator->setTempDirectory(dirname(TMP_DIR)); // shared container for performance purposes
        $configurator->setDebugMode(true);

        $configurator->addParameters([
            //'appDir' => __DIR__ . '/../../app',
        ]);

        //$configurator->addConfig(__DIR__ . '/../app/config/config.neon');
        $configurator->addConfig(CONFIG_DIR . '/tests.neon');

        $configurator->addDynamicParameters([
            'env' => getenv(),
        ]);

        return $configurator->createContainer();
    }

}