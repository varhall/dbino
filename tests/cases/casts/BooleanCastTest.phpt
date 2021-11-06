<?php

namespace Tests\Cases\Casts;

use Tester\Assert;
use Tests\Engine\ContainerTestCase;
use Varhall\Dbino\Casts\AttributeCastFactory;
use Varhall\Dbino\Model;
use Varhall\Dbino\Casts\BooleanCast;

require_once __DIR__ . '/../../bootstrap.php';

class BooleanCastTest extends ContainerTestCase
{
    public function testFactory()
    {
        $factory = new AttributeCastFactory($this->getContainer());

        Assert::type(BooleanCast::class, $factory->create('bool'));
        Assert::type(BooleanCast::class, $factory->create('boolean'));
    }

    public function testGet()
    {
        $Cast = new BooleanCast();

        Assert::true($Cast->get(\Mockery::mock(Model::class), null, 1));
        Assert::true($Cast->get(\Mockery::mock(Model::class), null, '1'));

        Assert::false($Cast->get(\Mockery::mock(Model::class), null, 0));
        Assert::false($Cast->get(\Mockery::mock(Model::class), null, '0'));
    }
}

(new BooleanCastTest())->run();
