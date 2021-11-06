<?php

namespace Varhall\Dbino\Collections;

use Varhall\Dbino\Configuration;
use Varhall\Utilino\Collections\ICollection;

/**
 * Nette Database extended class used for collection of related objects
 *
 * @author Ondrej Sibrava <sibrava@varhall.cz>
 */
class GroupedCollection extends \Nette\Database\Table\GroupedSelection implements ICollection
{
    use CollectionTrait;
    
    public function __construct(\Nette\Database\Table\GroupedSelection $selection, string $class)
    {
        foreach (get_object_vars($selection) as $property => $value) {
            if (property_exists(self::class, $property))
                $this->$property = &$selection->$property;
        }

        $this->class = $class;
    }
}
