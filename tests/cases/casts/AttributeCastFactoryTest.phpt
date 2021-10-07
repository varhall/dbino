<?php

namespace Tests\Cases\Casts;

use Tester\Assert;
use Tests\Engine\BaseTestCase;
use Varhall\Dbino\Casts\AttributeCastFactory;
use Varhall\Dbino\Casts\StringCast;

require_once __DIR__ . '/../../bootstrap.php';

class AttributeCastFactoryTest extends BaseTestCase
{
    public function testName()
    {
        $factory = new AttributeCastFactory();

        Assert::type(StringCast::class, $factory->create('string'));
    }

    public function testClass()
    {
        $factory = new AttributeCastFactory();

        Assert::type(StringCast::class, $factory->create(StringCast::class));
    }

    public function testObject()
    {
        $factory = new AttributeCastFactory();
        $Cast = new StringCast();

        Assert::same($Cast, $factory->create($Cast));
    }
}

(new AttributeCastFactoryTest())->run();
