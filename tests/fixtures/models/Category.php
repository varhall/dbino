<?php

namespace Varhall\Dbino\Tests\Models;

use Varhall\Dbino\Traits\TreeNode;

class Category extends \Varhall\Dbino\Model
{
    use TreeNode;

    protected function table()
    {
        return 'categories';
    }
}
