<?php

namespace Varhall\Dbino\Casts;

use Nette\DI\Container;
use Nette\InvalidArgumentException;

class AttributeCastFactory
{
    /** @var Container */
    protected $container;

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
        'hash'      => HashCast::class
    ];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function create($args): AttributeCast
    {
        if (is_string($args))
            $args = explode(':', $args);

        if (!is_array($args))
            throw new InvalidArgumentException('Cast argument must be string or array');

        $class = array_shift($args);

        $args = array_map(function($x) {
            return is_string($x) ? explode(',', $x) : (array) $x;
        }, $args);
        $args = array_merge(...$args);

        if (array_key_exists($class, $this->casts))
            $class = $this->casts[$class];

        if (!is_subclass_of($class, AttributeCast::class))
            throw new InvalidArgumentException("Required Cast {$class} is not type of " . AttributeCast::class);

        return $this->container->createInstance($class, $args);
    }
}