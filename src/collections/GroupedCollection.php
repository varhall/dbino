<?php

namespace Varhall\Dbino\Collections;

use Nette\Database\Table\Selection;

class GroupedCollection extends Collection
{
    public function __construct(Selection $selection, string $class, array $scopes)
    {
        parent::__construct($selection, $class);

        foreach ($scopes as $scope) {
            $scope->filter($this);
        }
    }
}
