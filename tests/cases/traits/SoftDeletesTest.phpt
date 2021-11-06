<?php

namespace Tests\Cases\Traits;

use Nette\MemberAccessException;
use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Tests\Models\Author;
use Varhall\Dbino\Tests\Models\Tag;

require_once __DIR__ . '/../../bootstrap.php';

class SoftDeletesTest extends DatabaseTestCase
{
    public function testDelete()
    {
        Author::find(1)->delete();
        Assert::null(Author::find(1));
    }

    public function testDeleteForce()
    {
        Author::find(1)->forceDelete();
        Assert::null(Author::find(1));
    }

    public function testRestore()
    {
        Author::find(1)->delete();
        Assert::null(Author::find(1));

        Author::withTrashed()->get(1)->restore();
        Assert::type(Author::class, Author::find(1));
    }

    public function testWithTrashed()
    {
        $expected = [
            'John',
            'Martin',
            'Karoline',
            'Hans'
        ];

        $result = Author::withTrashed()->map(function($item) {
            return $item->name;
        });

        Assert::equal($expected, $result->toArray());
    }

    public function testOnlyTrashed()
    {
        $expected = [
            'Hans'
        ];

        $result = Author::onlyTrashed()->map(function($item) {
            return $item->name;
        });

        Assert::equal($expected, $result->toArray());
    }

    public function testNotSupported()
    {
        $expected = [
            [ 'id' => 1, 'name' => 'JavaScript' ],
            [ 'id' => 2, 'name' => 'MySQL' ],
            [ 'id' => 3, 'name' => 'XML' ],
            [ 'id' => 4, 'name' => 'PHP' ],
        ];

        Assert::equal($expected, Tag::all()->toArray());

        Assert::exception(function() {
            Tag::withTrashed()->toArray();
        }, MemberAccessException::class);

        Assert::exception(function() {
            Tag::onlyTrashed()->toArray();
        }, MemberAccessException::class);

    }
}

(new SoftDeletesTest())->run();
