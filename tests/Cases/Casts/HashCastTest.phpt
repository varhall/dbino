<?php

namespace Varhall\Dbino\Tests\Cases;

use Nette\Security\Passwords;
use Tester\Assert;
use Tests\Engine\ContainerTestCase;
use Varhall\Dbino\Casts\AttributeCastFactory;
use Varhall\Dbino\Casts\HashCast;
use Varhall\Dbino\Model;

require_once __DIR__ . '/../../bootstrap.php';

class HashCastTest extends ContainerTestCase
{
    public function testFactory()
    {
        $factory = new AttributeCastFactory($this->getContainer());

        Assert::type(HashCast::class, $factory->create('hash'));
    }

    public function testSet()
    {
        $passwords = \Mockery::mock(Passwords::class);
        $passwords->shouldReceive('hash')->andReturn('xxx');

        $cast = new HashCast($passwords);

        Assert::equal('xxx', $cast->set(\Mockery::mock(Model::class), null, 'foo'));
    }
}

(new HashCastTest())->run();
