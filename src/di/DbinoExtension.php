<?php

namespace Varhall\Dbino\DI;

use Nette\DI\CompilerExtension;
use Varhall\Dbino\Casts\AttributeCastFactory;
use Varhall\Dbino\Dbino;
use Varhall\Dbino\Mutators\MutatorFactory;

class DbinoExtension extends CompilerExtension
{
    public function loadConfiguration()
    {
        $config = $this->config;
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('service.casts'))->setType(AttributeCastFactory::class);
        $builder->addDefinition($this->prefix('dbino'))->setType(Dbino::class);

        $this->getInitialization()->addBody('\Varhall\Dbino\Dbino::initialize($this->getByType(?));', [ Dbino::class ]);
    }
}
