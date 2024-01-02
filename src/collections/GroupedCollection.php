<?php

namespace Varhall\Dbino\Collections;

use Nette\Database\Table\Selection;
use Varhall\Dbino\Configuration;

class GroupedCollection extends Collection
{
    public function __construct(Selection $selection, Configuration $configuration)
    {
        parent::__construct($selection, $configuration);

        foreach ($configuration->scopes as $scope) {
            $scope->filter($this);
        }
    }
}
