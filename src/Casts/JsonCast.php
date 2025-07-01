<?php

namespace Varhall\Dbino\Casts;


use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Varhall\Dbino\Model;
use Varhall\Utilino\Utils\JsonObject;

class JsonCast extends AttributeCast
{
    const NULLABLE      = 'nullable';
    const PRIMITIVE     = 'primitive';

    public $nullable    = false;
    public $primitive   = false;


    public function __construct(...$options)
    {
        foreach ([ self::NULLABLE, self::PRIMITIVE ] as $option) {
            $this->$option = in_array($option, $options);
        }
    }

    public function get(Model $model, $property, $value, $args)
    {
        $defaults = $args['defaults'] ?? [];

        if ($this->nullable && $value === null)
            return $value;

        try {
            if (is_string($value) && !empty($value))
                $value = \Nette\Utils\Json::decode($value, Json::FORCE_ARRAY);

            else if ($value instanceof JsonObject)
                $value = $value->toArray();

            return is_array($value) || !$this->primitive
                ? new JsonObject(array_merge((array) $defaults, (array) $value))
                : $value;

        } catch (JsonException $ex) {
            if ($this->primitive)
                return $value;

            throw $ex;
        }
    }

    public function set(Model $model, $property, $value)
    {
        return $value || !$this->nullable ? Json::encode($value ?? []) : null;
    }
}