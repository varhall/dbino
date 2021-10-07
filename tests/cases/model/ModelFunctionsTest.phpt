<?php

namespace Tests\Cases\Model;

use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Tests\Models\Author;

require_once __DIR__ . '/../../bootstrap.php';

class ModelFunctionsTest extends DatabaseTestCase
{
    public function testInstance()
    {
        $expected = [
            'name'      => 'Johann',
            'surname'   => 'Mauer',
            'web'       => 'https://www.jmau.de',
            'email'     => 'johan@jmau.de'
        ];

        $instance = Author::instance($expected);

        Assert::equal($expected, $instance->toArray());

        foreach ($expected as $property => $value) {
            Assert::equal($value, $instance->$property);
        }
    }

    public function testFill()
    {
        $expected = [
            'name'      => 'Johann',
            'surname'   => 'Mauer',
            'web'       => 'https://www.jmau.de',
            'email'     => 'johan@jmau.de'
        ];

        $instance = Author::instance()->fill($expected);

        foreach ($expected as $property => $value) {
            Assert::equal($expected[$property], $instance->$property);
        }
    }

    public function testToArray()
    {
        $date = new DateTime();
        $expected = [
            'name'      => 'Johann',
            'surname'   => 'Mauer',
            'birthdate' => $date->format('c')
        ];

        $instance = Author::instance([
            'name'      => $expected['name'],
            'surname'   => $expected['surname'],
            'password'  => '123456',
            'birthdate' => $date
        ]);

        Assert::equal($expected, $instance->toArray());
    }

    public function testToJson()
    {
        $date = new DateTime();
        $expected = [
            'name'      => 'Johann',
            'surname'   => 'Mauer',
            'birthdate' => $date->format('c')
        ];

        $instance = Author::instance([
            'name'      => $expected['name'],
            'surname'   => $expected['surname'],
            'password'  => '123456',
            'birthdate' => $date
        ]);

        Assert::equal(Json::encode($expected), $instance->toJson());
    }

    public function testIsNew()
    {
        Assert::true(Author::instance()->isNew());
        Assert::false(Author::find(1)->isNew());
    }

    public function testIsSaved()
    {
        Assert::false(Author::instance()->isSaved());
        Assert::false(Author::instance([ 'name' => 'Uwe' ])->isSaved());
        Assert::false(Author::find(1)->fill([ 'name' => 'Uwe' ])->isSaved());

        Assert::true(Author::find(1)->isSaved());
    }
    
    public function testColumns()
    {
        $expected = [
            'id',
            'name',
            'surname',
            'web',
            'email',
            'password',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        Assert::equal($expected, Author::columns());
    }
}

(new ModelFunctionsTest())->run();