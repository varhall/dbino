<?php

namespace Tests\Cases\Traits;

use Tester\Assert;
use Tests\Engine\DatabaseTestCase;
use Varhall\Dbino\Tests\Models\Category;
use Varhall\Utilino\Collections\ICollection;

require_once __DIR__ . '/../../bootstrap.php';

class TreeNodeTest extends DatabaseTestCase
{
    private function assertName(array $expected, ICollection $collection)
    {
        $data = $collection->map(function($item) { return $item->name; })->toArray();
        Assert::equal($expected, $data);
    }

    public function testAll()
    {
        $expected = [
            'Computers',
            'RAM',
            'Drives',
            'M.2',
            'SSD',
            'HDD',
            'Processors',
            'Tablets',
            'Phones',
            'Android',
            'iOS'
        ];

        $this->assertName($expected, Category::all());
    }

    public function testRoots()
    {
        $expected = [
            'Computers',
            'Tablets',
            'Phones',
        ];

        $this->assertName($expected, Category::roots());
    }

    public function testParent()
    {
        $category = Category::find(4);
        Assert::equal('Drives', $category->parent_node->name);
    }

    public function testRoot()
    {
        $category = Category::find(4);
        Assert::equal('Computers', $category->root_node->name);
    }

    public function testChildren()
    {
        $expected = [
            'Computers',
            'RAM',
            'Drives',
            'M.2',
            'SSD',
            'HDD',
            'Processors',
        ];

        $this->assertName($expected, Category::find(1)->descendants(true));
    }

    public function testAncestors()
    {
        $expected = [
            'Computers',
            'Drives'
        ];

        $this->assertName($expected, Category::find(4)->ancestors());
    }

    public function testAncestorsAndSelf()
    {
        $expected = [
            'Computers',
            'Drives',
            'HDD'
        ];

        $this->assertName($expected, Category::find(4)->ancestors(true));
    }

    public function testDescendants()
    {
        $expected = [
            'RAM',
            'Drives',
            'M.2',
            'SSD',
            'HDD',
            'Processors',
        ];

        $this->assertName($expected, Category::find(1)->descendants());
    }

    public function testDescendantsAndSelf()
    {
        $expected = [
            'Computers',
            'RAM',
            'Drives',
            'M.2',
            'SSD',
            'HDD',
            'Processors',
        ];

        $this->assertName($expected, Category::find(1)->descendants(true));
    }

    public function testPath()
    {
        Assert::equal('Computers', Category::find(1)->path());
        Assert::equal('Computers > Drives', Category::find(3)->path());
        Assert::equal('Computers > Drives > SSD', Category::find(5)->path());
    }

    public function testCreate()
    {
        $category = Category::create([ 'name' => 'TV' ]);

        Assert::null($category->parent);
        Assert::equal(23, $category->lpos);
        Assert::equal(24, $category->rpos);
    }
}

(new TreeNodeTest())->run();
