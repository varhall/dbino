<?php

namespace Varhall\Dbino\Casts;

use Nette\InvalidArgumentException;

class AttributeCastFactory
{
    protected $casts = [
        'bool'      => BooleanCast::class,
        'boolean'   => BooleanCast::class,
        'int'       => IntegerCast::class,
        'integer'   => IntegerCast::class,
        'double'    => DoubleCast::class,
        'float'     => FloatCast::class,
        'real'      => FloatCast::class,
        'number'    => FloatCast::class,
        'string'    => StringCast::class,
        'json'      => JsonCast::class,
    ];

    public function register($key, AttributeCast $cast): void
    {
        $this->casts[$key] = $cast;
    }

    public function create($type): AttributeCast
    {
        if (is_string($type) && array_key_exists($type, $this->casts))
            $type = $this->casts[$type];

        if (!is_subclass_of($type, AttributeCast::class))
            throw new InvalidArgumentException("Required Cast {$type} is not type of " . AttributeCast::class);

        return is_string($type) ? new $type() : $type;
    }
}