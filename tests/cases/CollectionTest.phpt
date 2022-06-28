<?php

namespace Tests\Cases;

use Tester\Assert;
use Tester\Expect;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Collections\Collection;
use Varhall\Dbino\Dbino;
use Varhall\Dbino\Tests\Models\Book;

require_once __DIR__ . '/../bootstrap.php';

class CollectionTest extends DatabaseTestCase
{
    /** @var Collection */
    private $collection;

    public function setUp()
    {
        parent::setUp();

        $this->collection = $this->dbino->repository(Book::class)->all();
    }

    public function testCreateModel()
    {
        Assert::equal(5, $this->collection->count());

        foreach ($this->collection as $item) {
            Assert::type(Book::class, $item);
        }
    }

    public function testAsArray()
    {
        $data = $this->collection->asArray();

        foreach ($this->collection as $item) {
            Assert::type(Book::class, $item);
        }

        Assert::equal(5, $this->collection->count());
    }

    public function testToArray()
    {
        $expected = [
            [
                'id' => 1,
                'author_id' => 1,
                'title' => 'PHP Tips & Tricks',
                'written' => Expect::type('string'),
                'available' => false,
                'price' => 50.0,
                'created_at' => Expect::type('string'),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'author_id' => 1,
                'title' => 'MySQL Queries',
                'written' => Expect::type('string'),
                'available' => true,
                'price' => 80.0,
                'created_at' => Expect::type('string'),
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ];

        $data = $this->collection->where('id', [ 1, 2 ])->toArray();
        Assert::equal($expected, $data);
    }

    public function testWhereIfFalse()
    {
        $expected = [
            'PHP Tips & Tricks',
            'MySQL Queries',
            'Einfach JavaScript',
            'Web programming',
            'Oracle',
            //'Death Code'
        ];

        $condition = false;
        $data = $this->collection->whereIf($condition, 'price <= ?', 30);

        Assert::equal($expected, $data->map(function($item) { return $item->title; })->toArray());
    }

    public function testWhereIfTrue()
    {
        $expected = [
            'Einfach JavaScript',
            'Web programming',
            //'Death Code'
        ];

        $condition = true;
        $data = $this->collection->whereIf($condition, 'price <= ?', 30);

        Assert::equal($expected, $data->map(function($item) { return $item->title; })->toArray());
    }

    public function testWhereCustom()
    {
        $expected = [
            'MySQL Queries',
            'Einfach JavaScript',
            'Web programming',
            'Oracle',
        ];

        $data = $this->collection->whereAvailable(true);

        Assert::equal($expected, $data->map(function($item) { return $item->title; })->toArray());
    }

    public function testEach()
    {
        $expected = [
            'PHP Tips & Tricks',
            'MySQL Queries',
            'Einfach JavaScript',
            'Web programming',
            'Oracle',
            //'Death Code'
        ];

        $this->collection->each(function($item, $key, $index) use ($expected) {
            Assert::equal($expected[$index], $item->title);
            Assert::equal($key, $item->id);
        });
    }

    public function testEvery()
    {
        Assert::false($this->collection->every(function($item) {
            return $item->price > 100;
        }));

        Assert::true($this->collection->every(function($item) {
            return $item->price < 100;
        }));
    }

    public function testAny()
    {
        Assert::true($this->collection->any(function($item) {
            return $item->price > 50;
        }));

        Assert::false($this->collection->any(function($item) {
            return $item->price > 100;
        }));
    }

    public function testChunk()
    {
        $i = 0;
        $this->collection->chunk(2, function($data, $index) use (&$i) {
            Assert::equal($i, $index);
            Assert::equal([ $i + 1, $i + 2 ], $data->toArray());

            $i++;
        });
    }

}

(new CollectionTest())->run();
