<?php

namespace Varhall\Dbino\Tests\Cases;

use Tester\Assert;
use Tests\Engine\BaseTestCase;
use Varhall\Dbino\Casts\AttributeCastFactory;
use Varhall\Dbino\Model;
use Varhall\Dbino\Casts\StringCast;

require_once __DIR__ . '/../../bootstrap.php';

class StringCastTest extends BaseTestCase
{
    public function testFactory()
    {
        $factory = new AttributeCastFactory();

        Assert::type(StringCast::class, $factory->create('string'));
    }

    public function testGet()
    {
        $Cast = new StringCast();

        Assert::equal('5', $Cast->get(\Mockery::mock(Model::class), null, 5));
        Assert::equal('5.3', $Cast->get(\Mockery::mock(Model::class), null, 5.3));
    }
}

(new StringCastTest())->run();
