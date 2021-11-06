<?php

namespace Varhall\Dbino\Collections;


use Nette\NotImplementedException;
use Varhall\Dbino\Configuration;
use Varhall\Dbino\Repository;

class ManyToManyCollection extends GroupedCollection
{
    /**
     * student_course
     *
     * @var string
     */
    protected $intermediateTable = NULL;

    /**
     * courses
     *
     * @var string
     */
    protected $referencedTable = NULL;

    /**
     * student_id
     *
     * @var string
     */
    protected $foreignColumn = NULL;

    /**
     * course_id
     *
     * @var string
     */
    protected $referenceColumn = NULL;

    /**
     * student_id = 1
     *
     * @var mixed
     */
    protected $foreignValue = NULL;


    /**
     * Synchronization function override
     *
     * @var callable
     */
    protected $syncFunc = NULL;

    /**
     * Hook called before synchronization
     *
     * @var callable
     */
    protected $beforeSyncFunc = NULL;

    /**
     * Hook called after synchronization
     *
     * @var callable
     */
    protected $afterSyncFunc = NULL;


    public function __construct(\Nette\Database\Table\GroupedSelection $selection,
                                $intermediateTable, $referencedTable, $foreignColumn, $referenceColumn, $foreignValue,
                                string $class)
    {
        parent::__construct($selection, $class);

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

    public function attach($values)
    {
        $indb = array_map(
            function($item) { return $item->{$this->referenceColumn}; },
            $this->intermediateTable()->where("{$this->referenceColumn} IN ?", (array) $values)->select($this->referenceColumn)->fetchAll()
        );

        $values = array_diff((array) $values, $indb);

        if (empty($values))
            return;

        $values = array_map(function($item) {
            return [
                $this->foreignColumn    => $this->foreignValue,
                $this->referenceColumn  => $item
            ];
        }, $values);

        if (count($values) === 1)
            $values = array_pop($values);

        $this->intermediateTable()->insert($values);
    }

    public function detach($values = NULL)
    {
        $intermediate = $this->intermediateTable();

        if (!empty($values))
            $intermediate->where($this->referenceColumn, $values);

        $intermediate->delete();
    }

    public function sync($values)
    {
        if (is_array($values))
            $values = array_unique($values);

        if ($this->beforeSyncFunc)
            call_user_func_array($this->beforeSyncFunc, [ $this, &$values ]);


        if (!!$this->syncFunc && is_callable($this->syncFunc)) {
            call_user_func_array($this->syncFunc, [ $this, &$values ]);

        } else {
            $this->detach();
            $this->attach($values);
        }


        if ($this->afterSyncFunc)
            call_user_func_array($this->afterSyncFunc, [ $this, &$values ]);
    }

    public function beforeSync(callable $func)
    {
        $this->beforeSyncFunc = $func;

        return $this;
    }

    public function afterSync(callable $func)
    {
        $this->afterSyncFunc = $func;

        return $this;
    }

    public function customSync(callable $func)
    {
        $this->syncFunc = $func;

        return $this;
    }


    /***************************************** METHOD OVERRIDES *******************************************************/

    public function getSql(): string
    {
        $sql = parent::getSql();
        $join = "LEFT JOIN {$this->referencedTable} ON {$this->intermediateTable}.{$this->referenceColumn} = {$this->referencedTable}.id";

        $sql = preg_replace('/(from [^ ]+)/i', '$1 ' . $join, $sql);

        return $sql;
    }

    public function insert(iterable $data)
    {
        throw new NotImplementedException('Not implemented yet');
    }


    public function update(iterable $data): int
    {
        throw new NotImplementedException('Not implemented yet');
    }


    public function delete(): int
    {
        throw new NotImplementedException('Not implemented yet');
    }
}