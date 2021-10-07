<?php

namespace Varhall\Testino;

use Nette\Configurator;
use Nette\DI\Container;

class Initializer
{
    /** @var Container */
    protected $container = null;

    /** @var Configurator */
    public $configurator = null;

    public $sql = [];

    public function addSql($file)
    {
        $this->sql[] = $file;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function ready()
    {
        $this->container = $this->configurator->createContainer();
    }
}