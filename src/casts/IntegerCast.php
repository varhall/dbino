<?php

namespace Varhall\Dbino\Casts;


use Varhall\Dbino\Model;

class IntegerCast extends AttributeCast
{
    public function get(Model $model, $property, $value, $args)
    {
        return intval($value);
    }
}