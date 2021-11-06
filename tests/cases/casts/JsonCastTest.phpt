<?php

namespace Varhall\Dbino\Tests\Cases;

use Nette\Utils\Json;
use Tester\Assert;
use Tests\Engine\ContainerTestCase;
use Varhall\Dbino\Casts\AttributeCastFactory;
use Varhall\Dbino\Model;
use Varhall\Dbino\Casts\JsonCast;

require_once __DIR__ . '/../../bootstrap.php';

class JsonCastTest extends ContainerTestCase
{
    public function testFactory()
    {
        $factory = new AttributeCastFactory($this->getContainer());

        Assert::type(JsonCast::class, $factory->create('json'));
    }

    public function testGetObject()
    {
        $expected = [
            'name'      => 'Mark',
            'surname'   => 'Hellmann',
            'age'       => 53
        ];
        $Cast = new JsonCast();

        $result = $Cast->get(\Mockery::mock(Model::class), null, Json::encode($expected));
        Assert::equal($expected, $result->toArray());
    }

    public function testGetRequiredNull()
    {
        $Cast = new JsonCast([]);

        $result = $Cast->get(\Mockery::mock(Model::class), null, null);
        Assert::equal([], $result->toArray());
    }

    public function testGetNullableNull()
    {
        $Cast = new JsonCast([], [ JsonCast::OPTIONS_NULLABLE => true ]);

        $result = $Cast->get(\Mockery::mock(Model::class), null, null);
        Assert::null($result);
    }

    public function testGetDefaults()
    {
        $defaults = [
            'name'      => 'Mark',
            'surname'   => 'Hellmann',
        ];
        $data = [
            'age'       => 53
        ];

        $Cast = new JsonCast($defaults);

        $result = $Cast->get(\Mockery::mock(Model::class), null, Json::encode($data));
        Assert::equal(array_merge($defaults, $data), $result->toArray());
    }

    public function testGetPrimitiveValue()
    {
        $Cast = new JsonCast([], [ JsonCast::OPTIONS_PRIMITIVE => true ]);

        $result = $Cast->get(\Mockery::mock(Model::class), null, '5');
        Assert::equal(5, $result);
    }

    public function testGetPrimitiveObject()
    {
        $expected = [
            'name'      => 'Mark',
            'surname'   => 'Hellmann',
            'age'       => 53
        ];

        $Cast = new JsonCast([], [ JsonCast::OPTIONS_PRIMITIVE => true ]);

        $result = $Cast->get(\Mockery::mock(Model::class), null, Json::encode($expected));
        Assert::equal($expected, $result->toArray());
    }

    public function testSetObject()
    {
        $data = [
            'name'      => 'Mark',
            'surname'   => 'Hellmann',
            'age'       => 53
        ];
        $Cast = new JsonCast();

        $result = $Cast->set(\Mockery::mock(Model::class), null, $data);
        Assert::equal(Json::encode($data), $result);
    }

    public function testSetRequiredNull()
    {
        $Cast = new JsonCast([]);

        $result = $Cast->set(\Mockery::mock(Model::class), null, null);
        Assert::equal(Json::encode([]), $result);
    }

    public function testSetNullableNull()
    {
        $Cast = new JsonCast([], [ JsonCast::OPTIONS_NULLABLE => true ]);

        $result = $Cast->set(\Mockery::mock(Model::class), null, null);
        Assert::null($result);
    }

    public function testSetPrimitiveValue()
    {
        $Cast = new JsonCast([], [ JsonCast::OPTIONS_PRIMITIVE => true ]);

        $result = $Cast->set(\Mockery::mock(Model::class), null, 5);
        Assert::equal(Json::encode(5), $result);
    }

    public function testSerializer()
    {
        $serializer = function($value) {
            return array_flip($value);
        };

        $Cast = new JsonCast([], [ JsonCast::OPTIONS_SERIALIZER => $serializer ]);

        $result = $Cast->set(\Mockery::mock(Model::class), null, [ 'key' => 'value' ]);
        Assert::equal(Json::encode([ 'value' => 'key' ]), $result);
    }
}

(new JsonCastTest())->run();
