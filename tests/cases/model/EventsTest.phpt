<?php

namespace Tests\Cases\Model;

use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Events\DeleteArgs;
use Varhall\Dbino\Events\InsertArgs;
use Varhall\Dbino\Events\UpdateArgs;
use Varhall\Dbino\Tests\Models\Book;
use Varhall\Dbino\Tests\Repositories\BooksRepository;

require_once __DIR__ . '/../../bootstrap.php';

class EventsTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        BooksRepository::$eventsInjection = [];
    }

    public function testCreate_Model()
    {
        $book = Book::instance([
            'author_id' => 1,
            'title'     => 'Sample book',
            'written'   => new \DateTime(),
            'available' => true,
            'price'     => 30
        ]);

        $book->on('creating', function(InsertArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::null($args->id);
        });

        $book->on('saving', function(InsertArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::null($args->id);
        });

        $book->on('created', function(InsertArgs $args) use ($book) {
            Assert::equal($book->id, $args->id);
        });

        $book->on('saved', function(InsertArgs $args) use ($book) {
            Assert::equal($book->id, $args->id);
        });

        $book->save();
    }

    public function testCreate_Repository()
    {
        $book = Book::instance([
            'author_id' => 1,
            'title'     => 'Sample book',
            'written'   => new \DateTime(),
            'available' => true,
            'price'     => 30
        ]);

        BooksRepository::$eventsInjection['creating'] = function(InsertArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::null($args->id);
        };

        BooksRepository::$eventsInjection['saving'] = function(InsertArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::null($args->id);
        };

        BooksRepository::$eventsInjection['created'] = function(InsertArgs $args) use ($book) {
            Assert::equal($book->id, $args->id);
        };

        BooksRepository::$eventsInjection['saved'] = function(InsertArgs $args) use ($book) {
            Assert::equal($book->id, $args->id);
        };

        $book->save();
    }

    public function testUpdate_Model()
    {
        $book = Book::find(1)->fill([ 'title' => 'Test book' ]);

        $book->on('updating', function(UpdateArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::equal($book->id, $args->id);
        });

        $book->on('saving', function(UpdateArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::equal($book->id, $args->id);
        });

        $book->on('updated', function(UpdateArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::equal($book->id, $args->id);
        });

        $book->on('saved', function(UpdateArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::equal($book->id, $args->id);
        });

        $book->save();
    }

    public function testUpdate_Repository()
    {
        $book = Book::find(1)->fill([ 'title' => 'Test book' ]);

        BooksRepository::$eventsInjection['updating'] = function(UpdateArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::equal($book->id, $args->id);
        };

        BooksRepository::$eventsInjection['saving'] = function(UpdateArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::equal($book->id, $args->id);
        };

        BooksRepository::$eventsInjection['updated'] = function(UpdateArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::equal($book->id, $args->id);
        };

        BooksRepository::$eventsInjection['saved'] = function(UpdateArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::equal($book->id, $args->id);
        };

        $book->save();
    }

    public function testDelete_Model()
    {
        $book = Book::find(1);

        $book->on('deleting', function(DeleteArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::equal($book->id, $args->id);
        });

        $book->on('deleted', function(DeleteArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::equal($book->id, $args->id);
        });

        $book->delete();
    }

    public function testDelete_Repository()
    {
        $book = Book::find(1);

        BooksRepository::$eventsInjection['deleting'] = function(DeleteArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::equal($book->id, $args->id);
        };

        BooksRepository::$eventsInjection['deleted'] = function(DeleteArgs $args) use ($book) {
            Assert::same($book, $args->instance);
            Assert::equal($book->id, $args->id);
        };

        $book->delete();
    }
}

(new EventsTest())->run();
