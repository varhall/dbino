<?php

namespace Varhall\Dbino\Tests\Models;

use Varhall\Dbino\Traits\Scope;

/**
 * Post test model
 *
 * @author Ondrej Sibrava <sibrava@varhall.cz>
 */
class Post extends \Varhall\Dbino\Model
{
    use Scope;

    // configuration 

    protected function scopeDefinition(): array
    {
        return [
            'customer_id' => 1
        ];
    }

    protected function table()
    {
        return 'posts';
    }

}
