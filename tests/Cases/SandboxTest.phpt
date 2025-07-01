<?php

namespace Tests\Cases;

use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Collections\Collection;
use Varhall\Dbino\Tests\Models\Book;
use Varhall\Utilino\Collections\ArrayCollection;

require_once __DIR__ . '/../bootstrap.php';

class SandboxTest extends DatabaseTestCase
{
    /** @var Collection */
    private $collection;

    public function setUp()
    {
        parent::setUp();

        $this->collection = $this->dbino->repository(Book::class)->all();
    }


    public function testDummy()
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

}

(new SandboxTest())->run();
