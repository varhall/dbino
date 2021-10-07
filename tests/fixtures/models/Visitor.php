<?php

namespace Varhall\Dbino\Tests\Models;

use Varhall\Dbino\Plugins\UuidPlugin;

/**
 * Author test model
 *
 * @author Ondrej Sibrava <sibrava@varhall.cz>
 */
class Visitor extends \Varhall\Dbino\Model
{
    // configuration 

    protected function plugins()
    {
        return [
            new UuidPlugin()
        ];
    }
    
    protected function table()
    {
        return 'visitors';
    }

}
