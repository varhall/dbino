<?php

namespace Varhall\Dbino\Casts;


use Varhall\Dbino\Model;

class BooleanCast extends AttributeCast
{
    public function get(Model $model, $property, $value, $args)
    {
        return boolval($value);
    }
}