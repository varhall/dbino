<?php

namespace Varhall\Dbino\Tests\Models;

use Varhall\Dbino\Plugins\TimestampPlugin;
use Varhall\Dbino\Traits\SoftDeletes;
use Varhall\Dbino\Traits\Timestamps;

/**
 * Author test model
 *
 * @author Ondrej Sibrava <sibrava@varhall.cz>
 */
class Author extends \Varhall\Dbino\Model
{
    use SoftDeletes;
    use Timestamps;

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function books()
    {
        return $this->hasMany(Book::class, 'author_id');
    }
    
    public function getFullNameAttribute()
    {
        return "{$this->name} {$this->surname}";
    }
    
    // configuration

    protected function hiddenAttributes()
    {
        return [
            'password'
        ];
    }
    
    protected function table()
    {
        return 'authors';
    }

}
