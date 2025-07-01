<?php

namespace Varhall\Dbino\DI;

use Nette\DI\CompilerExtension;

class DbinoExtension extends CompilerExtension
{
    public function loadConfiguration()
    {
        $this->compiler->loadDefinitionsFromConfig(
            $this->loadFromFile(__DIR__ . '/dbino.neon')['services'],
        );

        $this->initialization->addBody('\Varhall\Dbino\Dbino::initialize($this);');
    }
}
