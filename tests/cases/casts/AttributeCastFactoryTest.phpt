<?php

namespace Tests\Cases\Casts;

use Tester\Assert;
use Tests\Engine\ContainerTestCase;
use Varhall\Dbino\Casts\AttributeCastFactory;
use Varhall\Dbino\Casts\StringCast;

require_once __DIR__ . '/../../bootstrap.php';

class AttributeCastFactoryTest extends ContainerTestCase
{
    public function testName()
    {
        $factory = new AttributeCastFactory($this->getContainer());

        Assert::type(StringCast::class, $factory->create('string'));
    }

    public function testClass()
    {
        $factory = new AttributeCastFactory($this->getContainer());

        Assert::type(StringCast::class, $factory->create(StringCast::class));
    }

    public function testObject()
    {
        $factory = new AttributeCastFactory($this->getContainer());
        $cast = new StringCast();

        Assert::same($cast, $factory->create($cast));
    }
}

(new AttributeCastFactoryTest())->run();
