<?php

namespace Tests\Cases\Plugins;

use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Tests\Models\Visitor;

require_once __DIR__ . '/../../bootstrap.php';

class UuidPluginTest extends DatabaseTestCase
{
    public function testCreate()
    {
        $visitor = Visitor::create([ 'name' => 'Steiner' ]);

        Assert::match('#^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$#i', $visitor->id);
        Assert::equal('Steiner', Visitor::find($visitor->id)->name);
    }
}

(new UuidPluginTest())->run();
