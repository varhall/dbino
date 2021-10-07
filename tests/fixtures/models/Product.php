<?php

namespace Varhall\Dbino\Tests\Models;

/**
 * Product test model
 *
 * @author Ondrej Sibrava <sibrava@varhall.cz>
 */
class Product extends \Varhall\Dbino\Model
{
    protected $casts = [
        'published'     => 'bool',
        'info'          => 'json'
    ];

    // configuration 

    protected function defaults()
    {
        return [
            'published' => true,
            'info'      => [
                'condition'     => 'new',
                'identifier'    => '',
                'warranty'      => 24
            ]
        ];
    }

    protected function table()
    {
        return 'products';
    }

}
