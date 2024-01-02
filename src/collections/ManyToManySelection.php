<?php

namespace Varhall\Dbino\Collections;


use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\NotSupportedException;
use Varhall\Dbino\Traits\Events;

class ManyToManySelection extends GroupedSelection
{
    use Events;

    protected string $intermediateTable;

    protected string $referencedTable;

    protected string $foreignColumn;

    protected string $referenceColumn;

    protected mixed $foreignValue;


    public function __construct(\Nette\Database\Table\GroupedSelection $selection,
                                string $intermediateTable,
                                string $referencedTable,
                                string $foreignColumn,
                                string $referenceColumn,
                                mixed $foreignValue)
    {
        foreach (get_object_vars($selection) as $property => $value) {
            if (property_exists(self::class, $property))
                $this->$property = &$selection->$property;
        }


        $this->intermediateTable = $intermediateTable;
        $this->referencedTable = $referencedTable;
        $this->foreignColumn = $foreignColumn;
        $this->referenceColumn = $referenceColumn;
        $this->foreignValue = $foreignValue;
    }


    public function intermediateTable()
    {
        return $this->context->table($this->intermediateTable)->where($this->foreignColumn, $this->foreignValue);
    }

    public function attach($values): void
    {
        $indb = array_map(
            function($item) { return $item->{$this->referenceColumn}; },
            $this->intermediateTable()->where("{$this->referenceColumn} IN ?", (array) $values)->select($this->referenceColumn)->fetchAll()
        );

        $values = array_diff((array) $values, $indb);

        if (empty($values)) {
            return;
        }

        $values = array_map(function($item) {
            return [
                $this->foreignColumn    => $this->foreignValue,
                $this->referenceColumn  => $item
            ];
        }, $values);

        if (count($values) === 1) {
            $values = array_pop($values);
        }

        $this->intermediateTable()->insert($values);
    }

    public function detach($values = null): void
    {
        $intermediate = $this->intermediateTable();

        if (!empty($values)) {
            $intermediate->where($this->referenceColumn, $values);
        }

        $intermediate->delete();
    }

    public function sync($values, callable $func = null)
    {
        if (is_array($values)) {
            $values = array_unique($values);
        }

        if (!$func) {
            $func = function() use ($values) {
                $this->detach();
                $this->attach($values);
            };
        }

        // synchronize
        $this->raise('beforeSync', $this, $values);
        $func();
        $this->raise('afterSync', $this, $values);
    }


    /***************************************** METHOD OVERRIDES *******************************************************/

    public function getSql(): string
    {
        $sql = parent::getSql();
        $join = "LEFT JOIN {$this->referencedTable} ON {$this->intermediateTable}.{$this->referenceColumn} = {$this->referencedTable}.id";

        $sql = preg_replace('/(from [^ ]+)/i', '$1 ' . $join, $sql);

        return $sql;
    }

    public function insert(iterable $data): ActiveRow|array|int|bool
    {
        throw new NotSupportedException('Collection is readonly');
    }


    public function update(iterable $data): int
    {
        throw new NotSupportedException('Collection is readonly');
    }


    public function delete(): int
    {
        throw new NotSupportedException('Collection is readonly');
    }
}