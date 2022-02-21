<?php

namespace Varhall\Dbino\Casts;

use Varhall\Dbino\Model;

abstract class AttributeCast
{
    public function get(Model $model, $property, $value, $args)
    {
        return $value;
    }

    public function set(Model $model, $property, $value)
    {
        return $value;
    }
}