<?php

namespace Varhall\Testino;

use Nette\Configurator;
use Nette\Database\Connection;

class Testino
{
    /** @var Initializer */
    protected static $initializer = null;

    public static function initialize($func)
    {
        $initializer = new Initializer();
        $initializer->configurator = new Configurator();
        $initializer->configurator->setDebugMode(false);

        if (is_callable($func))
            call_user_func($func, $initializer);

        $initializer->ready();
        static::$initializer = $initializer;
    }

    public static function container()
    {
        return static::$initializer->getContainer();
    }

    public static function createDatabase()
    {
        return new Database(static::$initializer->getContainer()->getByType(Connection::class), static::$initializer->sql);
    }
}