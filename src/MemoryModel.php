<?php

namespace Varhall\Dbino;

use Varhall\Utilino\ISerializable;

class MemoryModel implements ISerializable
{
    protected $attributes = [];


    ////////////////////////////////////////////////////////////////////////////
    /// MAGIC METHODS                                                        ///
    ////////////////////////////////////////////////////////////////////////////

    public function __construct(array $values = [])
    {
        $this->fill($values);
    }

    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }

    public function &__get($name)
    {
        if (method_exists($this, $name)) {
            $result = call_user_func([ $this, $name ]);
            return $result;
        }

        return $this->attributes[$name];
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }



    ////////////////////////////////////////////////////////////////////////////
    /// STATIC METHODS                                                       ///
    ////////////////////////////////////////////////////////////////////////////

    public static function instance(array $values = [])
    {
        return new static($values);
    }



    ////////////////////////////////////////////////////////////////////////////
    /// PUBLIC INSTANCE METHODS                                              ///
    ////////////////////////////////////////////////////////////////////////////

    public function fill(array $values)
    {
        $this->attributes = $values;

        return $this;
    }

    public function toArray()
    {
        $array = $this->attributes;

        foreach ($array as $key => $value) {
            if (in_array($key, $this->hiddenAttributes()))
                unset($array[$key]);
        }

        return $array;
    }

    public function toJson()
    {
        return \Nette\Utils\Json::encode($this->toArray());
    }



    ////////////////////////////////////////////////////////////////////////////
    /// CONFIGURATION                                                        ///
    ////////////////////////////////////////////////////////////////////////////

    protected function hiddenAttributes()
    {
        return [];
    }
}