<?php

namespace Varhall\Dbino;

class Configuration
{
    protected Dbino $dbino;
    protected array $options;

    public function __construct(Dbino $dbino, array $options = [])
    {
        $this->dbino = $dbino;
        $this->options = $options;
    }

    /// MAGIC METHODS

    public function &__get(string $name)
    {
        $var = $this->options[$name] ?? null;
        return $var;
    }

    public function __set(string $name, $value): void
    {
        $this->options[$name] = $value;
    }


    /// PUBLIC METHODS

    public function getDbino(): Dbino
    {
        return $this->dbino;
    }

    public function getRepository(): Repository
    {
        return $this->dbino->repository($this->model);
    }
}