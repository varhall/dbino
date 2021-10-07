<?php

namespace Varhall\Dbino\Traits;

use Varhall\Dbino\Events\InsertArgs;
use Varhall\Dbino\Events\UpdateArgs;

trait Timestamps
{
    /// CONFIGURATION

    protected function timestampsColumns()
    {
        return [
            'created' => defined('static::CREATED_AT') ? static::CREATED_AT : 'created_at',
            'updated' => defined('static::UPDATED_AT') ? static::UPDATED_AT : 'updated_at'
        ];
    }

    protected function initializeTimestamps()
    {
        $columns = $this->timestampsColumns();

        $created = $columns['created'];
        $updated = $columns['updated'];

        $this->on('creating', function(InsertArgs $args) use ($created, $updated) {
            $this->$created = new \DateTime();
            $this->$updated = null;
        });

        $this->on('updating', function(UpdateArgs $args) use ($updated) {
            $this->$updated = new \DateTime();
        });
    }
}