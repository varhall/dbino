<?php

namespace Varhall\Dbino\Collections;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Varhall\Dbino\Dbino;
use Varhall\Dbino\Model;
use Varhall\Dbino\Traits\SoftDeletes;
use Varhall\Utilino\Collections\ArrayCollection;
use Varhall\Utilino\Collections\ICollection;
use Varhall\Utilino\Utils\Reflection;

/**
 * Nette Database collection extension
 *
 * @author Ondrej Sibrava <sibrava@varhall.cz>
 */
trait CollectionTrait
{
    /** @var string */
    protected $class;

    /** @var ?bool */
    protected $trashed = false;


    public function __call(string $name, array $arguments)
    {
        if (preg_match('/^where(.*)If$/i', $name))
            return call_user_func_array([ $this, 'whereIf' ], array_merge([$name], $arguments));

        $repository = Dbino::_repository($this->class);

        if (preg_match('/^where/i', $name) && method_exists($repository, $name))
            return call_user_func_array([ $repository, $name ], array_merge([$this], $arguments));
    }

    protected function whereIf($method, $condition, ...$params)
    {
        if (is_callable($condition) && !call_user_func($condition))
            return $this;

        if (!$condition)
            return $this;

        $method = preg_replace('/If$/i', '', $method);

        return call_user_func_array([$this, $method], $params);
    }

    /** @return static */
    public function withTrashed()
    {
        $this->trashed = null;
        return $this;
    }

    /** @return static */
    public function onlyTrashed()
    {
        $this->trashed = true;
        return $this;
    }

    /// OVERRIDEN METHODS

    protected function execute(): void
    {
        if ($this->trashed !== null && $this->isSoftDeletable()) {
            $negative = $this->trashed ? 'NOT' : '';
            $this->where("{$this->softDeleteColumn()} {$negative}", null);
        }

        $backup = $this->data;

        parent::execute();

        if ($backup) {
            $this->resync($backup);
        }
    }

    protected function createRow(array $row): ActiveRow
    {
        return $this->createModel($row, $this);
    }

    public function insert(iterable $data)
    {
        $result = parent::insert($data);

        if ($result instanceof Selection) {
            return new Collection($result, $this->class);

        } else if ($result instanceof ActiveRow) {
            $table = Reflection::readPrivateProperty($result, 'table');
            $data  = Reflection::readPrivateProperty($result, 'data');

            return $this->createModel($data, $table);
        }

        return $result;
    }


    /// PROTECTED METHODS

    protected function createModel(array $data, Selection $table): Model
    {
        $instance = Dbino::_model($this->class);

        Reflection::writePrivateProperty($instance, 'data', $data);
        Reflection::writePrivateProperty($instance, 'table', $table);

        return $instance;
    }

    protected function resync(array $backup): void
    {
        foreach ($this->rows as $row) {
            $signature = $row->getSignature();

            if (!$signature || !array_key_exists($signature, $backup))
                continue;

            $original = $backup[$signature];
            Reflection::writePrivateProperty($original, 'data', Reflection::readPrivateProperty($row, 'data'));

            $this->rows[$signature] = $original;
            $this->data[$signature] = $original;
        }
    }

    public function isSoftDeletable(): bool
    {
        return Reflection::hasTrait($this->class, SoftDeletes::class);
    }

    public function softDeleteColumn(): string
    {
        if (!$this->isSoftDeletable())
            throw new \Nette\NotSupportedException('Model does not support soft deletes');

        return Dbino::_config($this->class, 'softDeleteColumn');
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// ICollection interface
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Executes function for each item in collection
     *
     * @param callable $func args: $item
     * @return ICollection
     */
    public function each(callable $func)
    {
        $index = 0;

        foreach ($this->fetchAll() as $key => $value) {
            if (call_user_func_array($func, [ $value, $key, $index++ ]) === false)
                break;
        }

        return $this;
    }

    /**
     * Returns true if every item in collection matches given function
     *
     * @param callable $func args: $item
     * @return boolean
     */
    public function every(callable $func)
    {
        return $this->toArrayCollection()->every($func);
    }

    /**
     * Return true if any of items in collection matches given function
     *
     * @param callable $func
     * @return boolean
     */
    public function any(callable $func)
    {
        return $this->toArrayCollection()->any($func);
    }

    /**
     * Returns new collection where each item matches given function
     *
     * @param callable $func args: $item
     * @return ICollection
     */
    public function filter(callable $func)
    {
        return $this->toArrayCollection()->filter($func);
    }

    /**
     * Returns new colletion where each key is in given array or matches given function
     *
     * @param $keys array|callable
     * @return ICollection
     */
    public function filterKeys($keys)
    {
        return $this->toArrayCollection()->filterKeys($keys);
    }

    /**
     * Returns first item which matches function if given
     *
     * @param callable|null $func args: $item
     * @return mixed
     */
    public function first(callable $func = null)
    {
        if ($this->isEmpty())
            return null;

        return $func ? $this->filter($func)->first() : $this->fetch();
    }

    /**
     * @return Reduces level of collection
     */
    public function flatten()
    {
        return $this->toArrayCollection()->flatten();
    }

    /**
     * Runs the function for chunks of given size
     *
     * @param int $size Chunk size
     * @param callable $func Function
     * @return ICollection
     */
    public function chunk(int $size, callable $func)
    {
        $total = (clone $this)->count('*');

        for ($i = 0; $i < ceil($total / $size); $i++) {
            $chunk = (clone $this)->limit($size, $i * $size);

            call_user_func($func, $chunk, $i);
        }

        return $this;
    }

    /**
     * true if collection is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }

    /**
     * Collection of keys
     *
     * @return ICollection
     */
    public function keys()
    {
        return new ArrayCollection(array_keys($this->fetchPairs()));
    }

    /**
     * Returns last item which matches function if given
     *
     * @param callable|null $func args: $item
     * @return mixed
     */
    public function last(callable $func = null)
    {
        return $this->toArrayCollection()->last($func);
    }

    /**
     * Transforms each item using given function
     *
     * @param callable $func args: $item
     * @return ICollection
     */
    public function map(callable $func)
    {
        return $this->toArrayCollection()->map($func);
    }

    /**
     * Merge array or collection with current collection
     *
     * @param ICollection|array $collection
     * @return ICollection
     */
    public function merge($collection)
    {
        $array = ($collection instanceof Selection)
            ? $collection->fetchAll()
            : (
            ($collection instanceof ICollection)
                ? $collection->asArray()
                : $collection
            );

        return new ArrayCollection(array_merge($this->asArray(), $array));
    }

    /**
     * Fills current collection to required length with default value
     *
     * @param $size
     * @param $value
     * @return mixed
     */
    public function pad($size, $value)
    {
        return $this->toArrayCollection()->pad($size, $value);
    }

    /**
     * Passes current collection into given function and returns the function value
     *
     * @param callable $func args: $this
     * @return mixed
     */
    public function pipe(callable $func)
    {
        return call_user_func_array($func, [ $this ]);
    }

    /**
     * Removes and returns last item from collection
     *
     * @return mixed
     */
    public function pop()
    {
        return $this->toArrayCollection()->pop();
    }

    /**
     * Inserts value at the begining of the collection
     *
     * @param $value
     * @return mixed
     */
    public function prepend($value)
    {
        return $this->toArrayCollection()->prepend($value);
    }

    /**
     * Adds the value in the and of the collection
     *
     * @param $value
     * @return mixed
     */
    public function push($value)
    {
        return $this->toArrayCollection()->push($value);
    }

    /**
     * Accumulates value through all elements from the start to end
     *
     * @param callable $func
     * @param mixed|null $initial
     * @return mixed
     */
    public function reduce(callable $func, $initial = null)
    {
        return $this->toArrayCollection()->reduce($func, $initial);
    }

    public function reverse()
    {
        return $this->toArrayCollection()->reverse();
    }

    /**
     * Removes first value from the collection
     *
     * @return mixed
     */
    public function shift()
    {
        return $this->toArrayCollection()->shift();
    }

    /**
     * Sorts collection using given compare function
     *
     * @param callable $func args: $a, $b
     * @return ICollection
     */
    public function sort(callable $func)
    {
        return $this->toArrayCollection()->sort($func);
    }

    /**
     * Returns collection of values
     *
     * @return ICollection
     */
    public function values()
    {
        return new ArrayCollection(array_values($this->fetchPairs()));
    }

    public function search($value, callable $func = null)
    {
        $func = $func !== null ? $func : [$this->class, 'search'];

        return call_user_func($func, $this, $value);
    }

    public function asArray()
    {
        $this->refresh();
        return array_values($this->fetchAll());
    }

    public function toArray()
    {
        $result = [];

        foreach ($this as $item) {
            $result[] = $item->toArray();
        }

        return $result;
    }

    public function toJson()
    {
        return \Nette\Utils\Json::encode($this->toArray());
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }

    protected function toArrayCollection()
    {
        return new ArrayCollection($this->asArray());
    }

}
