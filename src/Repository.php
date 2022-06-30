<?php

namespace Varhall\Dbino;

use Nette\Database\Explorer;
use Nette\InvalidArgumentException;
use Varhall\Dbino\Collections\Collection;

class Repository
{
    /** @var string */
    protected $table;
    
    /** @var string */
    protected $model;

    /** @var Explorer */
    protected $explorer;

    /** @var array */
    protected $events       = [];


    /// GETTERS & SETTERS

    public function getTable(): string
    {
        return $this->table;
    }
    
    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }
    
    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getExplorer(): Explorer
    {
        return $this->explorer;
    }

    public function setExplorer(Explorer $explorer): self
    {
        $this->explorer = $explorer;
        return $this;
    }

    public function getEvents(): array
    {
        return $this->events;
    }


    /// PUBLIC METHODS
    
    public function all(): Collection
    {
        return new Collection($this->explorer->table($this->table), $this->model);
    }

    public function where($condition, ...$parameters): Collection
    {
        return call_user_func_array([$this->all(), 'where'], func_get_args());
    }

    public function find($id): ?Model
    {
        if (is_array($id))
            return $this->all()->wherePrimary($id);

        return $this->all()->get($id);
    }

    public function findOrFail($id): Model
    {
        $item = $this->find($id);

        if (!$item)
            throw new InvalidArgumentException('Object not found');

        return $item;
    }

    public function findOrDefault($id, array $data = []): Model
    {
        try {
            return $this->findOrFail($id);

        } catch (InvalidArgumentException $ex) {
            return $this->instance($data);
        }
    }

    public function search(Selection $collection, $value, array $args = [])
    {
        $columns = Dbino::_config($this->class, 'searchedColumns');

        if (empty($columns))
            return $collection;

        $query = [];
        $params = [];
        foreach ($columns as $column) {
            $query[] = "{$column} LIKE ?";
            $params[] = "%{$value}%";
        }

        if (!empty($args)) {
            call_user_func_array('array_push', array_merge([&$query], array_keys($args)));
            call_user_func_array('array_push', array_merge([&$params], array_values($args)));
        }

        return $collection->whereOr(array_combine($query, $params));
    }

    public function instance(array $data = []): Model
    {
        return Dbino::_model($this->model, $data);
    }

    public function create(array $data = []): Model
    {
        return $this->instance($data)->save();
    }

    public function columns(): array
    {
        $columns = $this->explorer->getConnection()->getDriver()->getColumns($this->table);

        return array_map(function($column) {
            return $column['name'];
        }, $columns);
    }

    public function on($event, callable $callback): void
    {
        $this->events[$event][] = $callback;
    }
}