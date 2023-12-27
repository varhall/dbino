<?php

namespace Varhall\Dbino\Traits;


use Varhall\Dbino\Collections\Collection;
use Varhall\Dbino\Events\DeleteArgs;
use Varhall\Dbino\Events\RestoreArgs;
use Varhall\Dbino\Scopes\ColumnScope;

trait SoftDeletes
{
    protected string $softDeleteColumn = 'deleted_at';

    public static function withTrashed(): Collection
    {
        return static::withoutScope('soft-delete');
    }

    public static function onlyTrashed(): Collection
    {
        $instance = static::instance([]);
        return static::withTrashed()->where($instance->softDeleteColumn . ' NOT', null);
    }

    protected function initializeSoftDeletes()
    {
        $this->addScope(new ColumnScope($this->softDeleteColumn, null), 'soft-delete');
    }

    public function delete(): int
    {
        $args = new DeleteArgs([
            'id'        => $this->getPrimary(),
            'instance'  => $this,
            'soft'      => true
        ]);

        $this->raise('deleting', $args);

        $this->update([
            $this->softDeleteColumn => new \Nette\Utils\DateTime()
        ]);

        $this->raise('deleted',  $args);

        return 1;
    }

    public function forceDelete(): int
    {
        return parent::delete();
    }

    public function restore()
    {
        $args = new RestoreArgs([
            'id'        => $this->getPrimary(),
            'instance'  => $this,
        ]);

        $this->raise('restoring', $args);

        $this->update([
            $this->softDeleteColumn => null
        ]);

        $this->raise('restored', $args);

        return $this;
    }

    public function isTrashed()
    {
        return $this->{$this->softDeleteColumn} !== null;
    }
}