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
        $cast = new JsonCast();

        $result = $cast->get(\Mockery::mock(Model::class), null, Json::encode($expected), null);
        Assert::equal($expected, $result->toArray());
    }

    public function testGetRequiredNull()
    {
        $cast = new JsonCast();

        $result = $cast->get(\Mockery::mock(Model::class), null, null, null);
        Assert::equal([], $result->toArray());
    }

    public function testGetNullableNull()
    {
        $cast = new JsonCast(JsonCast::NULLABLE);

        $result = $cast->get(\Mockery::mock(Model::class), null, null, null);
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

        $cast = new JsonCast();

        $result = $cast->get(\Mockery::mock(Model::class), null, Json::encode($data), [ 'defaults' => $defaults ]);
        Assert::equal(array_merge($defaults, $data), $result->toArray());
    }

    public function testGetPrimitiveValue()
    {
        $cast = new JsonCast(JsonCast::PRIMITIVE);

        $result = $cast->get(\Mockery::mock(Model::class), null, '5', null);
        Assert::equal(5, $result);
    }

    public function testGetPrimitiveObject()
    {
        $expected = [
            'name'      => 'Mark',
            'surname'   => 'Hellmann',
            'age'       => 53
        ];

        $cast = new JsonCast(JsonCast::PRIMITIVE);

        $result = $cast->get(\Mockery::mock(Model::class), null, Json::encode($expected), null);
        Assert::equal($expected, $result->toArray());
    }

    public function testSetObject()
    {
        $data = [
            'name'      => 'Mark',
            'surname'   => 'Hellmann',
            'age'       => 53
        ];
        $cast = new JsonCast();

        $result = $cast->set(\Mockery::mock(Model::class), null, $data);
        Assert::equal(Json::encode($data), $result);
    }

    public function testSetRequiredNull()
    {
        $cast = new JsonCast();

        $result = $cast->set(\Mockery::mock(Model::class), null, null);
        Assert::equal(Json::encode([]), $result);
    }

    public function testSetNullableNull()
    {
        $cast = new JsonCast(JsonCast::NULLABLE);

        $result = $cast->set(\Mockery::mock(Model::class), null, null);
        Assert::null($result);
    }

    public function testSetPrimitiveValue()
    {
        $cast = new JsonCast(JsonCast::PRIMITIVE);

        $result = $cast->set(\Mockery::mock(Model::class), null, 5);
        Assert::equal(Json::encode(5), $result);
    }

    /*public function testSerializer()
    {
        $serializer = function($value) {
            return array_flip($value);
        };

        $cast = new JsonCast([ JsonCast::OPTIONS_SERIALIZER => $serializer ]);

        $result = $cast->set(\Mockery::mock(Model::class), null, [ 'key' => 'value' ]);
        Assert::equal(Json::encode([ 'value' => 'key' ]), $result);
    }*/
}

(new JsonCastTest())->run();
