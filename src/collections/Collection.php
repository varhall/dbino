<?php

namespace Varhall\Dbino\Collections;

use Nette\Database\Table\Selection;
use Nette\InvalidArgumentException;
use Varhall\Dbino\Configuration;
use Varhall\Utilino\Collections\ICollection;

/**
 * Nette Database extended class for collection representation
 *
 * @author Ondrej Sibrava <sibrava@varhall.cz>
 */
class Collection extends \Nette\Database\Table\Selection implements ICollection
{    
    use CollectionTrait;

    /// MAGIC METHODS
    
    public function __construct(Selection $selection, string $class)
    {
        parent::__construct($selection->context, $selection->conventions, $selection->name, null);

        $this->cache = $selection->cache;
        $this->class = $class;
    }


}
