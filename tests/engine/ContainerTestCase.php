<?php

namespace Tests\Engine;

abstract class ContainerTestCase extends BaseTestCase
{
    use TestContainer;

    protected function setUp()
    {
        $this->getContainer();
    }
}