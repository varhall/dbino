<?php

namespace Varhall\Dbino\Tests\Models;

use Nette\Database\Table\Selection;
use Varhall\Dbino\Plugins\TimestampPlugin;
use Varhall\Dbino\Tests\Repositories\BooksRepository;
use Varhall\Dbino\Traits\SoftDeletes;
use Varhall\Dbino\Traits\Timestamps;

/**
 * Book test model
 *
 * @author Ondrej Sibrava <sibrava@varhall.cz>
 */
class Book extends \Varhall\Dbino\Model
{
    use SoftDeletes;
    use Timestamps;

    protected $casts = [
        'available' => 'bool',
        'price'     => 'float'
    ];

    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }
    
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'book_tags', 'book_id', 'tag_id');
    }
 
    // configuration

    protected function repository(): string
    {
        return BooksRepository::class;
    }

    protected function table()
    {
        return 'books';
    }
}
