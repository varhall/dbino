<?php

namespace Varhall\Dbino;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Varhall\Dbino\Casts\AttributeCast;
use Varhall\Dbino\Casts\AttributeCastFactory;
use Varhall\Dbino\Collections\Collection;
use Varhall\Dbino\Mutators\Mutator;
use Varhall\Dbino\Mutators\MutatorFactory;
use Varhall\Utilino\Utils\Reflection;

class Dbino
{
    /** @var Dbino */
    private static $instance;

    /** @var Explorer */
    protected $explorer;

    /** @var AttributeCastFactory */
    protected $casts;

    public function __construct(Explorer $explorer, AttributeCastFactory $casts)
    {
        $this->explorer = $explorer;
        $this->casts = $casts;
    }


    /// STATIC METHODS

    public static function initialize(Dbino $dbino): void
    {
        self::$instance = $dbino;
    }

    public static function instance(): Dbino
    {
        return self::$instance;
    }


    /// INSTANCE METHODS

    /*public function repository(string $class): Repository
    {
        return new Repository($class, $this);
    }*/

    public function explorer(): Explorer
    {
        return $this->explorer;
    }

    public function table($name): Selection
    {
        return $this->explorer->table($name);
    }

    public function cast($type): AttributeCast
    {
        return $this->casts->create($type);
    }


    public function model(string $class, array $data = []): Model
    {
        $instance = new $class($this);

        if (!empty($data))
            $instance->fill($data);

        return $instance;
    }

    public function collection(string $class): Collection
    {
        $table = Reflection::callPrivateMethod($this->model($class), 'table');
        return new Collection($this->explorer->table($table), $this, $class);
    }
}