<?php

namespace Tests\Engine;

class DatabaseTestCase extends BaseTestCase
{
    use TestDatabase;

    public function setUp()
    {
        $this->init();
    }
}
