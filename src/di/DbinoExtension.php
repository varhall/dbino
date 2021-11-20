<?php

namespace Varhall\Dbino\DI;

use Nette\DI\CompilerExtension;
use Varhall\Dbino\Repository;
use Varhall\Dbino\Mutators\MutatorFactory;

class DbinoExtension extends CompilerExtension
{
    public function loadConfiguration()
    {
        $this->compiler->loadDefinitionsFromConfig(
            $this->loadFromFile(__DIR__ . '/dbino.neon')['services'],
        );

        $this->initialization->addBody('\Varhall\Dbino\Dbino::$container = $this;');
    }
}
