<?php

namespace Varhall\Dbino\Traits;


use Varhall\Dbino\Events\InsertArgs;
use Varhall\Dbino\Model;

trait Uuid
{
    protected function initializeUuid(): void
    {
        $this->on('creating', function(InsertArgs $args) {
            $this->fillColumns($args->instance);
        });
    }

    private function fillColumns(Model $model): void
    {
        foreach ($this->uuidColumns() as $column) {
            if (empty($model->$column)) {
                $model->$column = $this->guid();
            }
        }
    }

    private function uuidColumns(): array
    {
        return array_keys(array_filter($this->casts, function($cast) {
            return $cast === 'uuid';
        }));
    }

    private function guid(): string
    {
        if (function_exists('com_create_guid') === true) {
            return strtolower(trim(com_create_guid(), '{}'));
        }

        $guid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
        return strtolower($guid);
    }
}