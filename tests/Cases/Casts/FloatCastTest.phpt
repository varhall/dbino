<?php

namespace Tests\Cases\Casts;

use Tester\Assert;
use Tests\Engine\ContainerTestCase;
use Varhall\Dbino\Casts\AttributeCastFactory;
use Varhall\Dbino\Model;
use Varhall\Dbino\Casts\FloatCast;

require_once __DIR__ . '/../../bootstrap.php';

class FloatCastTest extends ContainerTestCase
{
    public function testFactory()
    {
        $factory = new AttributeCastFactory($this->getContainer());

        Assert::type(FloatCast::class, $factory->create('real'));
        Assert::type(FloatCast::class, $factory->create('float'));
        Assert::type(FloatCast::class, $factory->create('number'));
    }

    public function testGet()
    {
        $Cast = new FloatCast();

        Assert::equal(5.0, $Cast->get(\Mockery::mock(Model::class), null, '5', []));
        Assert::equal(5.3, $Cast->get(\Mockery::mock(Model::class), null, '5.3', []));
    }
}

(new FloatCastTest())->run();
