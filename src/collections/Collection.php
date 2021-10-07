<?php

namespace Varhall\Dbino\Collections;

use Nette\Database\Table\Selection;
use Nette\InvalidArgumentException;
use Varhall\Dbino\Configuration;
use Varhall\Dbino\Dbino;
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
    
    public function __construct(Selection $selection, Dbino $dbino, string $class)
    {
        parent::__construct($selection->context, $selection->conventions, $selection->name, null);

        $this->cache = $selection->cache;
        $this->dbino = $dbino;
        $this->class = $class;
    }


    public function find($id)
    {
        if (is_array($id))
            return $this->where($this->getPrimary(), $id);

        return $this->get($id);
    }

    public function findOrFail($id)
    {
        $item = $this->find($id);

        if (!$item)
            throw new InvalidArgumentException('Object not found');

        return $item;
    }

    public function findOrDefault($id, array $data = [])
    {
        try {
            return $this->findOrFail($id);

        } catch (InvalidArgumentException $ex) {
            return $this->dbino->model($this->class, $data);
        }
    }
}
