<?php

namespace Tests\Cases\Casts;

use Tester\Assert;
use Tests\Engine\ContainerTestCase;
use Varhall\Dbino\Casts\AttributeCastFactory;
use Varhall\Dbino\Model;
use Varhall\Dbino\Casts\DoubleCast;

require_once __DIR__ . '/../../bootstrap.php';

class DoubleCastTest extends ContainerTestCase
{
    public function testFactory()
    {
        $factory = new AttributeCastFactory($this->getContainer());
        Assert::type(DoubleCast::class, $factory->create('double'));
    }

    public function testGet()
    {
        $Cast = new DoubleCast();

        Assert::equal(5.0, $Cast->get(\Mockery::mock(Model::class), null, '5'));
        Assert::equal(5.3, $Cast->get(\Mockery::mock(Model::class), null, '5.3'));
    }
}

(new DoubleCastTest())->run();
