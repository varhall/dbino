<?php

namespace Varhall\Dbino\Plugins;


use Varhall\Dbino\Events\InsertArgs;
use Varhall\Dbino\Model;

class UuidPlugin extends Plugin
{
    protected $columns = [];

    public function __construct($columns = 'id')
    {
        $this->columns = (array) $columns;
    }

    public function register(Model $model)
    {
        $model->on('creating', function(InsertArgs $args) {
            $this->fillColumns($args->instance);
        });
    }

    protected function fillColumns($model)
    {
        foreach ($this->columns as $column) {
            if (empty($model->$column))
                $model->$column = $this->guid();
        }
    }

    protected function guid()
    {
        if (function_exists('com_create_guid') === true) {
            return strtolower(trim(com_create_guid(), '{}'));
        }

        $guid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
        return strtolower($guid);
    }
}