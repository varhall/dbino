<?php

namespace Tests\Cases\Model;

use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Tests\Models\Author;
use Varhall\Dbino\Tests\Models\Book;
use Varhall\Dbino\Tests\Models\Tag;

require_once __DIR__ . '/../../bootstrap.php';

class RelationsTest extends DatabaseTestCase
{
    public function testBelongsTo()
    {
        $book = Book::find(1);
        $author = $book->author();

        Assert::equal($book->author_id, $author->id);
        Assert::equal('Smith', $author->surname);
    }

    public function testBelongsToUnsaved()
    {
        $book = Book::instance([ 'author_id' => 1 ]);
        $author = $book->author();

        Assert::equal($book->author_id, $author->id);
        Assert::equal('Smith', $author->surname);
    }

    public function testHasMany()
    {
        $author = Author::find(1);
        $books = $author->books();

        foreach ($books as $book) {
            Assert::type(Book::class, $book);
            Assert::equal($author->id, $book->author_id);
        }

        $expected = [
            'PHP Tips & Tricks',
            'MySQL Queries'
        ];

        Assert::equal($expected, $books->map(function($item) { return $item->title; })->toArray());
    }

    public function testBelongsToMany()
    {
        $book = Book::find(1);
        $tags = $book->tags();

        $expected = [
            'MySQL',
            'XML',
            'PHP'
        ];

        Assert::equal($expected, $tags->map(function($item) { return $item->name; })->toArray());
    }

    public function testBelongsToManySoftDeletes()
    {
        $tag = Tag::find(1);
        $books = $tag->books();

        $expected = [
            'Einfach JavaScript',
            'Web programming'
        ];

        Assert::equal($expected, $books->map(function($item) { return $item->title; })->toArray());
    }
}

(new RelationsTest())->run();
