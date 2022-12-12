<?php

namespace Tests\Cases\Traits;

use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Tests\Models\Post;

require_once __DIR__ . '/../../bootstrap.php';

class ScopeTest extends DatabaseTestCase
{
    public function testAll()
    {
        $expected = [
            'Hello world',
            'Good morning world',
            'Bye world'
        ];

        $result = Post::all()->map(function($item) {
            return $item->title;
        });

        Assert::equal($expected, $result->toArray());
    }

    public function testOutside()
    {
        $result = Post::find(3);

        Assert::null($result);
    }

    public function testInsert()
    {
        $post = Post::create([
            'title' => 'New post'
        ]);

        Assert::equal(1, $post->customer_id);
    }

    public function testUpdate()
    {
        $post = Post::find(1)
            ->fill([ 'title' => 'updated' ])
            ->save();

        Assert::equal(1, $post->customer_id);
    }
}

(new ScopeTest())->run();
