<?php

namespace Varhall\Dbino\Casts;


use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Varhall\Dbino\Model;
use Varhall\Utilino\Utils\JsonObject;

class JsonCast extends AttributeCast
{
    const OPTIONS_SERIALIZER    = 'serializer';
    const OPTIONS_NULLABLE      = 'nullable';
    const OPTIONS_PRIMITIVE     = 'primitive';

    public $serializer  = null;
    public $nullable    = false;
    public $primitive   = false;
    public $defaults    = [];


    public function __construct($defaults = [], $options = [])
    {
        foreach ($options as $property => $value) {
            $this->$property = $value;
        }

        $this->defaults = $defaults;
    }

    public function get(Model $model, $property, $value)
    {
        if ($this->nullable && $value === null)
            return $value;

        try {
            if (is_string($value) && !empty($value))
                $value = \Nette\Utils\Json::decode($value, Json::FORCE_ARRAY);

            else if ($value instanceof JsonObject)
                $value = $value->toArray();

            return is_array($value) || !$this->primitive
                ? new JsonObject(array_merge($this->defaults, (array) $value))
                : $value;

        } catch (JsonException $ex) {
            if ($this->primitive)
                return $value;

            throw $ex;
        }
    }

    public function set(Model $model, $property, $value)
    {
        if ($this->serializer && is_callable($this->serializer))
            $value = call_user_func($this->serializer, $value);

        return $value || !$this->nullable ? Json::encode($value ?? []) : null;
    }
}