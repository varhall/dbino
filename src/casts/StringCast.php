<?php

namespace Varhall\Dbino\Casts;


use Varhall\Dbino\Model;

class StringCast extends AttributeCast
{
    public function get(Model $model, $property, $value, $args)
    {
        return strval($value);
    }
}