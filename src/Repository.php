<?php

namespace Varhall\Dbino;

use Nette\Database\Explorer;
use Nette\InvalidArgumentException;
use Varhall\Dbino\Collections\Collection;
use Varhall\Dbino\Traits\Events;

class Repository
{
    use Events;

    protected Explorer $explorer;

    protected Configuration $configuration;


    /// GETTERS & SETTERS

    public function getExplorer(): Explorer
    {
        return $this->explorer;
    }

    public function setExplorer(Explorer $explorer): self
    {
        $this->explorer = $explorer;
        return $this;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(Configuration $configuration): self
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @deprecated
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function setup(): void
    {

    }

    /// PUBLIC METHODS
    
    public function all(): Collection
    {
        $collection = new Collection($this->explorer->table($this->configuration->table), $this->configuration);

        // conditional where
        $collection->on('call', function(Collection $collection, string $method, array $arguments) {
            $this->conditionalWhere($collection, $method, $arguments);
        });

        // dynamic where
        $collection->on('call', function(Collection $collection, string $method, array $arguments) {
            $this->dynamicWhere($collection, $method, $arguments);
        });

        foreach ($this->configuration->scopes as $scope) {
            $scope->filter($collection);
        }

        return $collection;
    }

    public function where($condition, ...$parameters): Collection
    {
        return call_user_func_array([$this->all(), 'where'], func_get_args());
    }

    public function withoutScope(string|array $name): Collection
    {
        $this->configuration->scopes = array_filter($this->configuration->scopes, fn($key) => !in_array($key, (array) $name), ARRAY_FILTER_USE_KEY);
        return static::all();
    }

    public function find($id): Model|Collection|null
    {
        if (is_array($id)) {
            return $this->all()->wherePrimary($id);
        }

        return $this->all()->get($id);
    }

    public function findOrFail($id): Model
    {
        $item = $this->find($id);

        if (!$item) {
            throw new InvalidArgumentException('Object not found');
        }

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

    public function instance(array $data = []): Model
    {
        $model = new ($this->configuration->model)($this->configuration->getDbino());
        return $model->fill($data);
    }

    public function create(array $data = []): Model
    {
        return $this->instance($data)->save();
    }

    public function columns(): array
    {
        $columns = $this->explorer->getConnection()->getDriver()->getColumns($this->configuration->table);
        return array_map(fn($column) => $column['name'], $columns);
    }


    protected function conditionalWhere(Collection $collection, string $method, array $arguments): void
    {
        if (!preg_match('/If$/i', $method)) {
            return;
        }

        if (!array_shift($arguments)) {
            return;
        }

        $method = preg_replace('/If$/i', '', $method);
        call_user_func_array([ $collection, $method ], $arguments);
    }

    protected function dynamicWhere(Collection $collection, string $method, array $arguments): void
    {
        $method = 'where' . ucfirst(strtolower($method));

        if (method_exists($this, $method)) {
            call_user_func_array([ $this, $method ], [ $collection, ...$arguments ]);
        }
    }
}