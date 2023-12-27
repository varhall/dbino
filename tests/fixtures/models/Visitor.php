<?php

namespace Varhall\Dbino\Tests\Models;

use Varhall\Dbino\Plugins\Uuid;
use Varhall\Dbino\Plugins\UuidPlugin;

/**
 * Author test model
 *
 * @author Ondrej Sibrava <sibrava@varhall.cz>
 */
class Visitor extends \Varhall\Dbino\Model
{
    use Uuid;

    protected $casts = [
        'id'    => 'uuid'
    ];

    // configuration 

    protected function table()
    {
        return 'visitors';
    }

}
