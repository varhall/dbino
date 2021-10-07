<?php

namespace Varhall\Dbino\Casts;

use Nette\Security\Passwords;
use Varhall\Dbino\Model;

class HashCast extends AttributeCast
{
    /** @var Passwords */
    protected $passwords;

    public function __construct(Passwords $passwords)
    {
        $this->passwords = $passwords;
    }

    public function set(Model $model, $property, $value)
    {
        return $this->passwords->hash($value);
    }
}