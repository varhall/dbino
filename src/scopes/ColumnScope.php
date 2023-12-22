<?php

namespace Varhall\Dbino\Scopes;

use Varhall\Dbino\Collections\Collection;

class ColumnScope implements Scope
{
    protected string $column;
    protected mixed $value;

    public function __construct(string $column, mixed $value)
    {
        $this->column = $column;
        $this->value = $value;
    }

    public function filter(Collection $selection): void
    {
        $selection->where($this->column, $this->value);
    }
}