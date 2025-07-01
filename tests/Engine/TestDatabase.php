<?php

namespace Tests\Engine;

use Nette\Database\Connection;
use Nette\SmartObject;

trait TestDatabase
{
    use SmartObject;
    use TestContainer;

    public $onCreate = [];
    public $onDrop = [];

    /** @var string */
    protected $database;

    /** @var Connection */
    protected $connection;

    protected function init()
    {
        $this->connection = $this->getContainer()->getByType(Connection::class);
        $this->database = '__testino__' . getmypid();

        $this->resetDatabase();

        register_shutdown_function(function () {
            $this->dropDatabase();
            $this->connection->disconnect();
        });
    }

    protected function resetDatabase()
    {
        $this->dropDatabase();
        $this->createDatabase();
    }

    private function createDatabase()
    {
        $this->connection->query("CREATE DATABASE {$this->database}");
        $this->connection->query("USE {$this->database}");

        $this->seed();

        $this->onCreate();
    }

    private function dropDatabase()
    {
        $this->connection->query("DROP DATABASE IF EXISTS {$this->database}");
        $this->onDrop();
    }

    private function seed()
    {
        foreach (scandir(FIXTURES_DIR) as $item) {
            $file = FIXTURES_DIR . '/' . $item;

            if (!is_file($file))
                continue;

            if (preg_match('/\.sql$/i', $file))
                $this->connection->query(file_get_contents($file));
        }
    }
}