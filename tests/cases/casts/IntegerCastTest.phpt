<?php

namespace Varhall\Dbino\Tests\Cases;

use Tester\Assert;
use Tests\Engine\BaseTestCase;
use Varhall\Dbino\Casts\AttributeCastFactory;
use Varhall\Dbino\Model;
use Varhall\Dbino\Casts\IntegerCast;

require_once __DIR__ . '/../../bootstrap.php';

class IntegerCastTest extends BaseTestCase
{
    public function testFactory()
    {
        $factory = new AttributeCastFactory();

        Assert::type(IntegerCast::class, $factory->create('int'));
        Assert::type(IntegerCast::class, $factory->create('integer'));
    }

    public function testGet()
    {
        $Cast = new IntegerCast();

        Assert::equal(5, $Cast->get(\Mockery::mock(Model::class), null, '5'));
    }
}

(new IntegerCastTest())->run();
