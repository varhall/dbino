<?php

namespace Varhall\Dbino;

class Configuration
{
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function &__get(string $name)
    {
        $var = $this->options[$name] ?? null;
        return $var;
    }

    public function __set(string $name, $value): void
    {
        $this->options[$name] = $value;
    }
}