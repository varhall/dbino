<?php

namespace Varhall\Dbino\Casts;


use Varhall\Dbino\Model;

class FloatCast extends AttributeCast
{
    public function get(Model $model, $property, $value)
    {
        return floatval($value);
    }
}