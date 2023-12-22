<?php

namespace Tests\Cases\Model;

use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Repository;
use Varhall\Dbino\Tests\Models\Author;
use Varhall\Dbino\Tests\Models\Book;
use Varhall\Dbino\Tests\Repositories\BooksRepository;

require_once __DIR__ . '/../../bootstrap.php';

class RepositoryTest extends DatabaseTestCase
{
    public function testGetRepository_Default()
    {
        $repository = Author::getRepository();

        Assert::equal(Repository::class, get_class($repository));
    }

    public function testGetRepository_Custom()
    {
        $repository = Book::getRepository();

        Assert::equal(BooksRepository::class, get_class($repository));
    }

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
