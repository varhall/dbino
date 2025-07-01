<?php

namespace Tests\Cases\Model;

use Nette\InvalidArgumentException;
use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Tests\Models\Author;
use Varhall\Dbino\Tests\Models\Book;

require_once __DIR__ . '/../../bootstrap.php';

class ReadTest extends DatabaseTestCase
{
    private function helperFind(callable $func)
    {
        $expected = [
            'id'            => 1,
            'name'          => 'John',
            'surname'       => 'Smith',
            'web'           => 'http://www.smith.com/',
            'email'         => 'john@smith.com',
            'created_at'    => '2017-10-20T13:35:02+02:00',
            'updated_at'    => null,
            'deleted_at'    => null
        ];

        $author = call_user_func($func);
        Assert::equal($expected, $author->toArray());
    }

    public function testFindValid()
    {
        $this->helperFind(function() {
            return Author::find(1);
        });
    }

    public function testFindInvalid()
    {
        Assert::null(Author::find(100));
    }

    public function testFindOrDefaultValid()
    {
        $this->helperFind(function() {
            return Author::findOrDefault(1);
        });
    }

    public function testFindOrDefaultInvalid()
    {
        $author = Author::findOrDefault(100);

        Assert::type(Author::class, $author);
        Assert::true($author->isNew());
    }

    public function testFindOrFailValid()
    {
        $this->helperFind(function() {
            return Author::findOrFail(1);
        });
    }

    public function testFindOrFailInvalid()
    {
        Assert::exception(function() {
            Author::findOrFail(100);
        }, InvalidArgumentException::class);
    }

    public function testAll()
    {
        $expected = [
            'John',
            'Martin',
            'Karoline',
        ];

        $result = Author::all()->map(function($item) {
            return $item->name;
        });

        Assert::equal($expected, $result->toArray());
    }


    public function testWhere()
    {
        $expected = [
            'PHP Tips & Tricks'
        ];

        $result = Book::where('available', false)->map(function($item) {
            return $item->title;
        });

        Assert::equal($expected, $result->toArray());
    }

    public function testWhereMultiple()
    {
        $expected = [
            'PHP Tips & Tricks',
            'MySQL Queries'
        ];

        $result = Book::where('id = ? OR id = ?', 1, 2)->map(function($item) {
            return $item->title;
        });
        
        Assert::equal($expected, $result->toArray());
    }
}

(new ReadTest())->run();
