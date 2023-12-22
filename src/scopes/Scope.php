<?php

namespace Varhall\Dbino\Scopes;

use Varhall\Dbino\Collections\Collection;

interface Scope
{
    public function filter(Collection $selection): void;
}