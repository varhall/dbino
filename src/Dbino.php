<?php

namespace Varhall\Dbino;

use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\NotSupportedException;
use Varhall\Dbino\Casts\AttributeCast;
use Varhall\Dbino\Casts\AttributeCastFactory;
use Varhall\Utilino\Utils\Reflection;

/**
 * @method static Repository _repository(string $class)
 * @method static Model _model(string $class, array $data = [])
 * @method static string _config(string $class, string $option)
 * @method static ?AttributeCast _cast($type)
 * @method static Explorer _explorer()
 */
class Dbino
{
    /** @var Container */
    public static $container;

    public static function __callStatic($name, $arguments)
    {
        $dbino = self::$container->getByType(static::class);
        $method = preg_replace('/^_/i', '', $name);

        if (preg_match('/^_/i', $name) && method_exists($dbino, $method))
            return call_user_func_array([ $dbino, $method ], $arguments);

        throw new NotSupportedException("Method " . get_class($dbino) . "::{$method} does not exist");
    }

    public function repository(string $class): Repository
    {
        $repositoryClass = $this->config($class, 'repository');
        $table = $this->config($class, 'table');

        $repository = self::$container->getByType($repositoryClass, false);

        if (!$repository)
            $repository = new $repositoryClass();

        if (!($repository instanceof Repository))
            throw new InvalidStateException('Repository is not instance of ' . Repository::class);

        return $repository
            ->setTable($table)
            ->setModel($class)
            ->setExplorer($this->explorer());
    }

    public function model(string $class, array $data = []): Model
    {
        $instance = new $class();

        if (!empty($data))
            $instance->fill($data);

        return $instance;
    }

    public function config(string $class, string $option): string
    {
        return Reflection::callPrivateMethod($this->model($class), $option);
    }

    public function cast($type): ?AttributeCast
    {
        return self::$container->getByType(AttributeCastFactory::class)->create($type);
    }

    public function explorer(): Explorer
    {
        return self::$container->getByType(Explorer::class);
    }
}