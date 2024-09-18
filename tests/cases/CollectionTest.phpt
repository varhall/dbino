<?php

namespace Tests\Cases;

use Tester\Assert;
use Tester\Expect;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Collections\Collection;
use Varhall\Dbino\Dbino;
use Varhall\Dbino\Tests\Models\Author;
use Varhall\Dbino\Tests\Models\Book;
use Varhall\Utilino\Collections\ArrayCollection;

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

        foreach ($data as $item) {
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

        $data = $this->collection->available(true);

        Assert::equal($expected, $data->map(function($item) { return $item->title; })->toArray());
    }

    public function testWhereCollectionArg()
    {
        $expected = [
            'PHP Tips & Tricks',
            'MySQL Queries',
            'Web programming',
            'Oracle',
        ];

        $authors = $this->dbino->repository(Author::class)
                        ->all()
                        ->where('name', [ 'John', 'Martin' ])
                        ->select('id');

        $data = $this->collection->where('author_id', $authors);

        Assert::equal($expected, $data->map(function($item) { return $item->title; })->toArray());
    }

    public function testWhereSelectionArg()
    {
        $expected = [
            'PHP Tips & Tricks',
            'MySQL Queries',
            'Web programming',
            'Oracle',
        ];

        $authors = $this->dbino->repository(Author::class)
                    ->all()
                    ->getSelection()
                    ->where('name', [ 'John', 'Martin' ])
                    ->select('id');

        $data = $this->collection->where('author_id', $authors);

        Assert::equal($expected, $data->map(function($item) { return $item->title; })->toArray());
    }

    // ICollection methods

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

    public function testFilter()
    {
        $expected = [
            'PHP Tips & Tricks',
            'Oracle',
        ];

        $result = $this->collection->filter(function($item) {
            return $item->price === 50.0;
        })->map(function($item) {
            return $item->title;
        });

        Assert::equal($expected, $result->toArray());
    }

    public function testFilterKeys()
    {
        $expected = [
            'PHP Tips & Tricks',
            'Einfach JavaScript',
            'Oracle',
        ];

        $result = $this->collection->filterKeys(function($key) {
            return $key % 2 === 0;
        })->map(function($item) {
            return $item->title;
        });

        Assert::equal($expected, $result->toArray());
    }

    public function testFirst()
    {
        Assert::equal(1, $this->collection->first()->id);
    }

    public function testFlatten()
    {
        $expected = [
            1, 2, 3, 4, 5
        ];

        $result = $this->collection->map(fn($x) => [ $x->id ])->flatten();

        Assert::equal($expected, $result->toArray());
    }

    public function testChunk()
    {
        $chunks = [
            [ 1, 2 ],
            [ 3, 4 ],
            [ 5 ],
        ];

        $i = 0;
        $this->collection->chunk(2, function($data, $index) use (&$i, $chunks) {
            Assert::equal($i, $index);
            Assert::equal($chunks[$i], $data->map(function($item) { return $item->id; })->toArray());

            $i++;
        });
    }

    public function testIsEmpty()
    {
        Assert::false($this->collection->isEmpty());
        Assert::true($this->collection->where('id', 100)->isEmpty());
    }

    public function testKeys()
    {
        Assert::equal([ 1, 2, 3, 4, 5 ], $this->collection->keys()->toArray());
    }



    public function testClone()
    {
        $collection = $this->collection->where('id', 1);
        $cloned = clone $collection;

        Assert::equal($collection->count(), $cloned->count());
        Assert::equal($collection->toArray(), $cloned->toArray());
    }

    // generate test methods for all ICollection methods of Collection class
    public function testCount()
    {
        Assert::equal(5, $this->collection->count());
    }

    public function testLast()
    {
        Assert::equal(5, $this->collection->last()->id);
    }

    public function testMap()
    {
        $expected = [
            'PHP Tips & Tricks',
            'MySQL Queries',
            'Einfach JavaScript',
            'Web programming',
            'Oracle',
            //'Death Code'
        ];

        $result = $this->collection->map(function($item) {
            return $item->title;
        });

        Assert::equal($expected, $result->toArray());
    }

    public function testMerge()
    {
        $expected = [
            'PHP Tips & Tricks',
            'MySQL Queries',
            'Einfach JavaScript',
            'Web programming',
            'Oracle',
            //'Death Code'
        ];

        $result = $this->collection->merge($this->collection)->map(function($item) {
            return $item->title;
        });

        Assert::equal([...$expected, ...$expected], $result->toArray());
    }

    public function testPad()
    {
        $expected = [
            1, 2, 3, 4, 5, 666, 666, 666, 666, 666
        ];

        $result = $this->collection->pad(10, (object) [ 'id' => 666 ])->map(fn($x) => $x->id);

        Assert::equal($expected, $result->toArray());
    }

    public function testPipe()
    {
        $expected = [
            'PHP Tips & Tricks',
            'MySQL Queries',
        ];

        $result = $this->collection->pipe(function($collection) {
            return $collection->where('id', [ 1, 2 ]);
        })->map(function($item) {
            return $item->title;
        });

        Assert::equal($expected, $result->toArray());
    }

    public function testPop()
    {
        $last = $this->collection->pop();
        Assert::equal('Oracle', $last->title);
    }

    public function testPrepend()
    {
        $expected = [
            'Death Code',
            'PHP Tips & Tricks',
            'MySQL Queries',
            'Einfach JavaScript',
            'Web programming',
            'Oracle',
        ];

        $result = $this->collection->prepend(Book::instance([ 'title' => 'Death Code' ]))->map(function($item) {
            return $item->title;
        });

        Assert::equal($expected, $result->toArray());
    }

    public function testPush()
    {
        $expected = [
            'PHP Tips & Tricks',
            'MySQL Queries',
            'Einfach JavaScript',
            'Web programming',
            'Oracle',
            'Death Code',
        ];

        $result = $this->collection->push(Book::instance([ 'title' => 'Death Code' ]))->map(function($item) {
            return $item->title;
        });

        Assert::equal($expected, $result->toArray());
    }

    public function testReduce()
    {
        $expected = 220.0;

        $result = $this->collection->reduce(function($carry, $item) {
            return $carry + $item->price;
        }, 0);

        Assert::equal($expected, $result);
    }

    public function testReverse()
    {
        $expected = [
            'Oracle',
            'Web programming',
            'Einfach JavaScript',
            'MySQL Queries',
            'PHP Tips & Tricks',
        ];

        $result = $this->collection->reverse()->map(function($item) {
            return $item->title;
        });

        Assert::equal($expected, $result->toArray());
    }

    public function testShift()
    {
        $first = $this->collection->shift();
        Assert::equal('PHP Tips & Tricks', $first->title);
    }

    public function testSort()
    {
        $expected = [
            'Einfach JavaScript',
            'MySQL Queries',
            'Oracle',
            'PHP Tips & Tricks',
            'Web programming',
            //'Death Code'
        ];

        $result = $this->collection->sort(function($a, $b) {
            return $a->title <=> $b->title;
        })->map(function($item) {
            return $item->title;
        });

        Assert::equal($expected, $result->toArray());
    }

    public function testValues()
    {
        Assert::equal([ 1, 2, 3, 4, 5 ], $this->collection->values()->map(function($item) { return $item->id; })->toArray());
    }




    public function testOffsetExists()
    {
        Assert::true(isset($this->collection[1]));
        Assert::false(isset($this->collection[100]));
    }

    public function testOffsetGet()
    {
        Assert::equal(1, $this->collection[1]->id);
        Assert::equal(2, $this->collection[2]->id);
        Assert::equal(3, $this->collection[3]->id);
        Assert::equal(4, $this->collection[4]->id);
        Assert::equal(5, $this->collection[5]->id);
    }

    public function testOffsetSet()
    {
        Assert::throws(fn() => $this->collection[1] = Book::instance([ 'title' => 'Death Code' ]), \Nette\NotSupportedException::class);
    }

    public function testOffsetUnset()
    {
        unset($this->collection[1]);
        Assert::equal(4, $this->collection->count());
    }









    public function testGetIterator()
    {
        $i = 0;
        foreach ($this->collection as $item) {
            Assert::equal($i + 1, $item->id);
            $i++;
        }
    }
















}

(new CollectionTest())->run();
