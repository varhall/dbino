<?php

namespace Tests\Cases\Casts;

use Tester\Assert;
use Tests\Engine\ContainerTestCase;
use Varhall\Dbino\Casts\AttributeCastFactory;
use Varhall\Dbino\Casts\StringCast;
use Varhall\Dbino\Casts\JsonCast;

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

    public function testArrayOptions()
    {
        $factory = new AttributeCastFactory($this->getContainer());
        $cast = [ 'json', 'nullable', 'primitive' ];

        $result = $factory->create($cast);
        Assert::type(JsonCast::class, $result);
        Assert::true($result->nullable);
        Assert::true($result->primitive);
    }

    public function testStringOptions()
    {
        $factory = new AttributeCastFactory($this->getContainer());

        $result = $factory->create('json:nullable,primitive');
        Assert::type(JsonCast::class, $result);
        Assert::true($result->nullable);
        Assert::true($result->primitive);
    }

    public function testEmptyOptions()
    {
        $factory = new AttributeCastFactory($this->getContainer());

        $result = $factory->create('json');
        Assert::type(JsonCast::class, $result);
        Assert::false($result->nullable);
        Assert::false($result->primitive);
    }
}

(new AttributeCastFactoryTest())->run();
