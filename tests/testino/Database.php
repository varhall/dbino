<?php

namespace Varhall\Testino;

use Nette\Database\Connection;
use Nette\SmartObject;

class Database
{
    use SmartObject;

    const PREFIX = '_testino__';

    public $onCreate    = [];
    public $onDrop      = [];

    public $name = null;

    public $files = [];

    /** @var Connection */
    protected $connection = null;

    public function __construct(Connection $connection, array $files = [], $prefix = self::PREFIX)
    {
        $this->connection = $connection;
        $this->files = $files;
        $this->name = $prefix . getmypid();

        $this->reset();

        register_shutdown_function(function () {
            $this->drop();
            $this->connection->disconnect();
        });
    }

    public function create()
    {
        $this->connection->query("CREATE DATABASE {$this->name}");
        $this->connection->query("USE {$this->name}");

        foreach ($this->files as $file) {
            $this->connection->query(file_get_contents($file));
        }

        $this->onCreate();
    }

    public function drop()
    {
        $this->connection->query("DROP DATABASE IF EXISTS {$this->name}");
        $this->onDrop();
    }

    public function reset()
    {
        $this->drop();
        $this->create();
    }
}