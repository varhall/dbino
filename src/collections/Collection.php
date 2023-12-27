<?php

namespace Varhall\Dbino\Collections;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\Database\Table\Selection;
use Varhall\Dbino\Configuration;
use Varhall\Dbino\Model;
use Varhall\Dbino\Traits\Events;
use Varhall\Utilino\Collections\ArrayCollection;
use Varhall\Utilino\Collections\ICollection;
use Varhall\Utilino\Utils\Reflection;

// @method int count(?string $column = null) Counts number of rows. If column is not provided returns count of result rows, otherwise runs new sql counting query.
// @method Selection limit(?int $limit, ?int $offset = null) Sets limit clause, more calls rewrite old values.

/**
 * Filtered table representation.
 * Selection is based on the great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @method Model|null get(mixed $key) Returns row specified by primary key.
 * @method Model|null fetch() Fetches single row object.
 * @method mixed fetchField(?string $column = null) Fetches single field.
 * @method array fetchPairs(string|int|null $key = null, string|int|null $value = null) Fetches all rows as associative array.
 * @method Model[] fetchAll() Fetches all rows.
 * @method array fetchAssoc(string $path) Fetches all rows and returns associative tree.
 * @method Collection select(string $columns, ...$params) Adds select clause, more calls appends to the end.
 * @method Collection wherePrimary(mixed $key) Adds condition for primary key.
 * @method Collection where(string|array $condition, ...$params) Adds where condition, more calls append with AND.
 * @method Collection joinWhere(string $tableChain, string $condition, ...$params) Adds ON condition when joining specified table, more calls append with AND.
 * @method Collection whereOr(array $parameters) Adds where condition using the OR operator between parameters.
 * @method Collection order(string $columns, ...$params) Adds order clause, more calls append to the end.
 * @method Collection page(int $page, int $itemsPerPage, &$numOfPages = null) Sets offset using page number, more calls rewrite old values.
 * @method Collection group(string $columns, ...$params) Sets group clause, more calls rewrite old value.
 * @method Collection having(string $having, ...$params) Sets having clause, more calls rewrite old value.
 * @method Collection alias(string $tableChain, string $alias) Aliases table.
 * @method mixed min(string $column) Returns minimum value from a column.
 * @method mixed max(string $column) Returns maximum value from a column.
 * @method mixed sum(string $column) Returns sum of values in a column.
 * @method Model|array|int|bool insert(iterable $data) Inserts row in a table. Returns ActiveRow or number of affected rows for Collection or table without primary key.
 * @method int update(iterable $data) Updates all rows in the result set.
 * @method int delete() Deletes all rows in the result set.
 * @method ActiveRow|false|null getReferencedTable(ActiveRow $row, ?string $table, ?string $column = null) Returns referenced row.
 * @method GroupedSelection|null getReferencingTable(string $table, ?string $column = null, int|string|null $active = null) Returns referencing rows.
 */
class Collection implements ICollection, \Iterator
{
    use Events;

    protected Configuration $configuration;

    protected Selection $table;

    /// MAGIC METHODS
    
    public function __construct(Selection $table, Configuration $configuration)
    {
        $this->table = $table;
        $this->configuration = $configuration;
    }

    public function __destruct()
    {
        $this->table->__destruct();
    }


    public function __clone()
    {
        $this->table = clone $this->table;
    }

    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this->table, $name)) {
            $result = call_user_func_array([$this->table, $name], $arguments);

            if ($result === $this->table) {
                return $this;

            } else if ($result instanceof ActiveRow) {
                return $this->createModel($result);
            }

            return $result;
        }

        return $this->raise('call', $this, $name, $arguments);
    }


    /// PUBLIC METHODS




    /// PRIVATE METHODS

    protected function createModel(ActiveRow $row): Model
    {
        return new ($this->configuration->model)($this->configuration->getDbino(), $row);
    }

    protected function toArrayCollection()
    {
        return new ArrayCollection($this->asArray());
    }

    /**
     * @deprecated Hopefully will not be used any more. Currently a dead code, taken from historic Collection class.
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
     */


    /// OVERRIDEN METHODS

    public function count(?string $column = null): int
    {
        return $this->table->count($column);
    }

    public function limit(?int $limit, ?int $offset = null)
    {
        $this->table->limit($limit, $offset);
        return $this;
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
        return new ArrayCollection(array_keys($this->fetchPairs($this->table->getPrimary())));
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
        return new ArrayCollection(array_values($this->fetchPairs($this->table->getPrimary())));
    }

    public function search($value, callable $func = null)
    {
        $func = $func !== null ? $func : [$this->configuration->model, 'search'];

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


    /// JsonSerializable implementation

    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }


    /// ArrayAccess implementation

    /**
     * Mimic row.
     * @param  string  $key
     * @param  ActiveRow  $value
     */
    public function offsetSet($key, $value): void
    {
        throw new \Nette\NotSupportedException('Collection is read-only.');
        //$this->table->offsetSet($key, $value);
    }


    /**
     * Returns specified row.
     * @param  string  $key
     */
    public function offsetGet($key): mixed
    {
        $result = $this->table->offsetGet($key);

        if ($result !== null) {
            return $this->createModel($result);
        }

        return $result;
    }


    /**
     * Tests if row exists.
     * @param  string  $key
     */
    public function offsetExists($key): bool
    {
        return $this->table->offsetExists($key);
    }


    /**
     * Removes row from result set.
     * @param  string  $key
     */
    public function offsetUnset($key): void
    {
        $this->table->offsetUnset($key);
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Iterator interface
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function rewind(): void
    {
        $this->table->rewind();
    }

    public function valid(): bool
    {
        return $this->table->valid();
    }

    public function key(): mixed
    {
        return $this->table->key();
    }

    public function current(): mixed
    {
        return $this->createModel($this->table->current());
    }

    public function next(): void
    {
        $this->table->next();
    }

}
