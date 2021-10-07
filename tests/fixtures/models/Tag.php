<?php

namespace Varhall\Dbino\Tests\Models;

/**
 * Tag test model
 *
 * @author Ondrej Sibrava <sibrava@varhall.cz>
 */
class Tag extends \Varhall\Dbino\Model
{
    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_tags', 'tag_id', 'book_id');
    }
    
    // configuration
    
    protected function table()
    {
        return 'tags';
    }

}
