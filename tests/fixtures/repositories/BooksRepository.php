<?php

namespace Varhall\Dbino\Tests\Repositories;

use Nette\Database\Table\Selection;
use Varhall\Dbino\Collections\Collection;
use Varhall\Dbino\Repository;
use Varhall\Dbino\Tests\Models\Book;

class BooksRepository extends Repository
{
    public static $eventsInjection = [];

    public function __construct()
    {
        foreach (static::$eventsInjection as $event => $func) {
            $this->on($event, $func);
        }
    }

    public function findAvailable(): Collection
    {
        return Book::all()->where('available', true);
    }

    public function whereAvailable(Selection $selection, $value)
    {
        return $selection->where('available', $value);
    }
}