<?php

namespace Varhall\Dbino\Tests\Repositories;

use Varhall\Dbino\Collections\Collection;
use Varhall\Dbino\Repository;
use Varhall\Dbino\Tests\Models\Book;

class BooksRepository extends Repository
{
    public static $eventsInjection = [];

    public function setup(): void
    {
        foreach (static::$eventsInjection as $event => $func) {
            $this->on($event, $func);
        }
    }

    public function findAvailable(): Collection
    {
        return $this->where('available', true);
    }

    public function whereAvailable(Collection $collection, $value)
    {
        return $collection->where('available', $value);
    }
}