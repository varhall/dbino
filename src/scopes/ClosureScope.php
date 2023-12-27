<?php

namespace Varhall\Dbino\Scopes;

use Varhall\Dbino\Collections\Collection;

class ClosureScope implements Scope
{
    private $filter;

    public function __construct(callable $filter)
    {
        $this->filter = $filter;
    }

    public function filter(Collection $collection): void
    {
        ($this->filter)($collection);
    }
}