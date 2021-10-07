<?php

namespace Tests\Cases\Traits;

use Nette\Utils\DateTime;
use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Tests\Models\Author;

require_once __DIR__ . '/../../bootstrap.php';

class TimestampsTest extends DatabaseTestCase
{
    public function testCreate()
    {
        $author = Author::create([
            'name' => 'John',
            'surname' => 'Smith',
            'web' => 'http://www.smith.com/',
            'email' => 'john@smith.com',
            'password' => '123456'
        ]);

        Assert::type(DateTime::class, $author->created_at);
        Assert::equal(date('Y-m-d'), $author->created_at->format('Y-m-d'));
        Assert::null($author->updated_at);
    }

    public function testUpdate()
    {
        $original = Author::find(1);
        $created = $original->created_at;
        $author = $original->fill([ 'name' => 'Hans' ])->save();

        Assert::type(DateTime::class, $author->updated_at);
        Assert::equal(date('Y-m-d'), $author->updated_at->format('Y-m-d'));
        Assert::equal($created, $author->created_at);
    }
}

(new TimestampsTest())->run();
