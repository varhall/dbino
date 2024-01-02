<?php

namespace Tests\Cases\Model;

use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Tests\Models\Book;

require_once __DIR__ . '/../../bootstrap.php';

class RepositoryTest extends DatabaseTestCase
{
    public function testRepositoryMethod()
    {
        $data = Book::findAvailable();

        $expected = [
            'MySQL Queries',
            'Einfach JavaScript',
            'Web programming',
            'Oracle',
        ];

        Assert::equal($expected, $data->map(function($item) { return $item->title; })->toArray());
    }

    public function testFindMultiple()
    {
        $data = Book::find([1, 2, 3]);

        $expected = [
            'PHP Tips & Tricks',
            'MySQL Queries',
            'Einfach JavaScript',
        ];

        Assert::equal($expected, $data->map(function($item) { return $item->title; })->toArray());
    }
}

(new RepositoryTest())->run();
