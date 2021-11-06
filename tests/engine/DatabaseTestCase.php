<?php

namespace Tests\Engine;

use Varhall\Dbino\Dbino;

abstract class DatabaseTestCase extends BaseTestCase
{
    use TestDatabase;

    /** @var Dbino */
    protected $dbino;

    public function setUp()
    {
        $this->init();
        $this->dbino = $this->getContainer()->getByType(Dbino::class);
    }
}
