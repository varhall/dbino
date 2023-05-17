<?php

namespace Varhall\Dbino\Traits;


use Varhall\Dbino\Collections\Collection;
use Varhall\Dbino\Dbino;
use Varhall\Dbino\Events\DeleteArgs;
use Varhall\Dbino\Events\RestoreArgs;

trait SoftDeletes
{
    public static function withTrashed(): Collection
    {
        return static::getRepository()->all()->withTrashed();
    }

    public static function onlyTrashed(): Collection
    {
        return static::getRepository()->all()->onlyTrashed();
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
            $this->softDeleteColumn() => new \Nette\Utils\DateTime()
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
            $this->softDeleteColumn() => NULL
        ]);

        $this->raise('restored', $args);

        return $this;
    }

    public function isTrashed()
    {
        return $this->{$this->softDeleteColumn()} !== null;
    }

    protected function softDeleteColumn()
    {
        return 'deleted_at';
    }
}