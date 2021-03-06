<?php

namespace Varhall\Dbino\Casts;


use Varhall\Dbino\Model;

class DoubleCast extends AttributeCast
{
    public function get(Model $model, $property, $value, $args)
    {
        return doubleval($value);
    }
}