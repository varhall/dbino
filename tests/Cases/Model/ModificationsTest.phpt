<?php

namespace Tests\Cases\Model;

use Nette\InvalidStateException;
use Tester\Assert;
use Tester\Expect;
use Tester\TestCase;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Dbino;
use Varhall\Dbino\Tests\Models\Author;
use Varhall\Dbino\Tests\Models\Tag;
use Varhall\Testino\Testino;

require_once __DIR__ . '/../../bootstrap.php';

class ModificationsTest extends DatabaseTestCase
{
    public function testCreate()
    {
        $expected = [
            'name'      => 'Johann',
            'surname'   => 'Mauer',
            'web'       => 'https://www.jmau.de',
            'email'     => 'johan@jmau.de'
        ];

        $instance = Author::create($expected);
        $author = Author::find($instance->id);

        Assert::equal(array_merge($expected, [
            'id'            => Expect::type('int'),
            'created_at'    => Expect::type('string'),
            'updated_at'    => null,
            'deleted_at'    => null
        ]), $author->toArray());
    }

    public function testUpdate()
    {
        $author = Author::find(1);

        $author->name = 'Hans';
        $author->surname = 'Schmied';
        $author->save();

        $result = Author::find(1)->toArray();

        Assert::equal(array_merge($author->toArray(), [
            'updated_at' => Expect::type('string')
        ]), $result);
    }

    public function testDelete()
    {
        Tag::find(1)->delete();
        Assert::null(Tag::find(1));
    }

    public function testDelete_unsaved()
    {
        $tag = Tag::instance([]);

        Assert::exception(function() use ($tag) {
            $tag->delete();
        }, InvalidStateException::class);
    }

    public function testSaveInsert()
    {
        Author::instance([
            'name'      => 'Johann',
            'surname'   => 'Mauer',
            'web'       => 'https://www.jmau.de',
            'email'     => 'johan@jmau.de'
        ])->save();

        $expected = [
            'John',
            'Martin',
            'Karoline',
            'Johann'
        ];

        $result = Author::all()->map(function($item) { return $item->name; })->toArray();
        Assert::equal($expected, $result);
    }

    public function testSaveUpdate()
    {
        Author::find(1)->fill([ 'name' => 'Michael' ])->save();

        $expected = [
            'Michael',
            'Martin',
            'Karoline'
        ];

        $result = Author::all()->map(function($item) { return $item->name; })->toArray();
        Assert::equal($expected, $result);
    }

    public function testSave_unsaved()
    {
        $author = Author::instance([]);

        Assert::same($author, $author->save());
    }

    public function testDuplicateEmpty()
    {
        $expected = [
            'id'            => 5,
            'name'          => 'John',
            'surname'       => 'Smith',
            'web'           => 'http://www.smith.com/',
            'email'         => 'john@smith.com',
            'created_at'    => Expect::type('string'),
            'updated_at'    => null,
            'deleted_at'    => null
        ];

        $clone = Author::find(1)->duplicate();
        Assert::equal($expected, $clone->toArray());
    }

    public function testDuplicateValues()
    {
        $expected = [
            'id'            => 5,
            'name'          => 'Johann',
            'surname'       => 'Schmied',
            'web'           => 'http://www.smith.com/',
            'email'         => 'john@smith.com',
            'created_at'    => Expect::type('string'),
            'updated_at'    => null,
            'deleted_at'    => null
        ];

        $clone = Author::find(1)->duplicate([ 'name' => 'Johann', 'surname' => 'Schmied' ]);
        Assert::equal($expected, $clone->toArray());
    }

    public function testDuplicate_unsaved()
    {
        $author = Author::instance([]);

        Assert::exception(function() use ($author) {
            $author->duplicate();
        }, InvalidStateException::class);
    }
}

(new ModificationsTest())->run();
