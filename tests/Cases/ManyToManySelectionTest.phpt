<?php

namespace Tests\Cases;

use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Collections\Collection;
use Varhall\Dbino\Collections\ManyToManySelection;
use Varhall\Dbino\Tests\Models\Book;

require_once __DIR__ . '/../bootstrap.php';

class ManyToManySelectionTest extends DatabaseTestCase
{
    /** @var Collection */
    private $collection;

    public function setUp()
    {
        parent::setUp();

        $this->collection = $this->dbino->repository(Book::class)->all();
    }


    public function testAttach()
    {
        $expected = [
            'JavaScript',
            'MySQL',
            'XML',
            'PHP',
        ];

        Book::find(1)->tags()->attach(1);

        $tags = Book::find(1)->tags()->map(function($item) {
            return $item->name;
        });

        Assert::equal($expected, $tags->toArray());
    }

    public function testDetach()
    {
        $expected = [
            'MySQL',
            'PHP',
        ];

        Book::find(1)->tags()->detach(3);

        $tags = Book::find(1)->tags()->map(function($item) {
            return $item->name;
        });

        Assert::equal($expected, $tags->toArray());
    }

    public function testSync()
    {
        $expected = [
            'JavaScript',
            'MySQL',
            'PHP',
        ];

        Book::find(1)->tags()->sync([ 1, 2, 4 ]);

        $tags = Book::find(1)->tags()->map(function($item) {
            return $item->name;
        });

        Assert::equal($expected, $tags->toArray());
    }

    public function testEvents()
    {
        $tags = [1, 2, 4];

        Book::find(1)->tags()
            ->on('beforeSync', function($collection, $values) use ($tags) {
                Assert::equal($tags, $values);
            })
            ->onAfterSync(function($collection, $values) use ($tags) {
                Assert::equal($tags, $values);
            })
            ->sync($tags);
    }

}

(new ManyToManySelectionTest())->run();
