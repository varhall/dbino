<?php

namespace Tests\Cases\Model;

use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Tests\Models\Author;
use Varhall\Dbino\Tests\Models\Product;

require_once __DIR__ . '/../../bootstrap.php';

class AttributesTest extends DatabaseTestCase
{
    public function testBoolean()
    {
        Assert::true(Product::find(1)->published);
        Assert::false(Product::find(3)->published);
    }

    public function testJson()
    {
        $expected = [
            'condition'     => 'new',
            'identifier'    => 'IP12',
            'warranty'      => 24
        ];

        Assert::equal($expected, Product::find(1)->info->toArray());
    }

    public function testWrite()
    {
        $info = [
            'condition'     => 'new',
            'identifier'    => 'XXX',
            'warranty'      => 24
        ];

        $expected = Product::create([
            'name'          => 'test',
            'availability'  => 'stocked',
            'published'     => true,
            'info'          => $info
        ]);

        Assert::equal($expected->toArray(), Product::find($expected->id)->toArray());
    }

    public function testTemporary()
    {
        $info = [
            'condition'     => 'new',
            'identifier'    => 'XXX',
            'warranty'      => 24
        ];

        $info2 = [
            'condition'     => 'used',
            'identifier'    => 'YYY',
            'warranty'      => 6
        ];

        $product = Product::instance([
            'name'          => 'test',
            'availability'  => 'stocked',
            'published'     => true,
            'info'          => $info
        ]);

        Assert::equal($info, $product->info->toArray());
        Assert::true($product->published);

        $product->info = $info2;
        $product->save();

        Assert::equal($info2, Product::find($product->id)->info->toArray());
    }

    public function testDefaults()
    {
        $defaults = [
            'condition'     => 'new',
            'identifier'    => '',
            'warranty'      => 24
        ];

        Assert::equal('unknown', Product::instance()->availability);
        Assert::true(Product::instance()->published);
        Assert::equal($defaults, Product::instance()->info->toArray());
    }

    public function testJsonDefaultsEmpty()
    {
        $expected = [
            'condition'     => 'new',
            'identifier'    => '',
            'warranty'      => 24
        ];

        Assert::equal($expected, Product::find(4)->info->toArray());
    }

    public function testJsonDefaultsPartial()
    {
        $expected = [
            'condition'     => 'used',
            'identifier'    => '',
            'warranty'      => 24
        ];

        Assert::equal($expected, Product::find(5)->info->toArray());
    }

    public function testIsset_saved()
    {
        $product = Product::find(1);

        Assert::true(isset($product->name));
        Assert::false(isset($product->unknown));
    }

    public function testIsset_unsaved()
    {
        $product = Product::instance([ 'name' => 'Markus' ]);

        Assert::true(isset($product->name));
        Assert::false(isset($product->unknown));
    }


    public function testOffsetGet()
    {
        $author = Author::find(1);

        Assert::equal('John', $author['name']);
    }

    public function testOffsetSet()
    {
        $author = Author::find(1);

        $author['name'] = 'foo';
        Assert::equal('foo', $author->name);
    }

    public function testOffsetIsset()
    {
        $author = Author::find(1);

        Assert::true(isset($author['name']));
        Assert::false(isset($author['unknown']));
    }

    public function testOffsetUnset()
    {
        $author = Author::find(1);

        Assert::throws(function() use ($author) {
            unset($author['name']);
        }, \Nette\NotSupportedException::class);
    }
}

(new AttributesTest())->run();
