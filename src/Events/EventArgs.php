<?php

namespace Varhall\Dbino\Events;

use Varhall\Dbino\Model;

abstract class EventArgs
{
    /** @var mixed */
    public $id = null;

    /** @var Model */
    public $instance = null;

    public function __construct($args = [])
    {
        foreach ($args as $name => $value) {
            if (property_exists(static::class, $name))
                $this->$name = $value;
        }
    }
}