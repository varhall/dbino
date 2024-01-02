<?php

namespace Varhall\Dbino;

use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Varhall\Dbino\Casts\AttributeCast;
use Varhall\Dbino\Casts\AttributeCastFactory;

class Dbino
{
    private static Container $_container;

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /// STATIC METHODS

    public static function initialize(Container $container): void
    {
        self::$_container = $container;
    }

    public static function instance(): self
    {
        return new static(static::$_container);
    }


    /// PUBLIC METHODS

    public function repository(string $model): Repository
    {
        if (!is_subclass_of($model, Model::class)) {
            throw new InvalidStateException('Class ' . $model . ' is not subclass of ' . Model::class);
        }

        $config = $model::configuration();
        $repository = $this->container->getByType($config->repository, false) ?? new ($config->repository)();

        if (!($repository instanceof Repository)) {
            throw new InvalidStateException('Repository is not instance of ' . Repository::class);
        }

        $repository->setConfiguration($config)->setExplorer($this->explorer());
        $repository->setup();

        return $repository;
    }

    public function cast($type): ?AttributeCast
    {
        return $this->container->getByType(AttributeCastFactory::class)->create($type);
    }

    public function explorer(): Explorer
    {
        return $this->container->getByType(Explorer::class);
    }
}