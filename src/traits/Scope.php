<?php

namespace Varhall\Dbino\Traits;

use Nette\Database\Table\Selection;
use Nette\InvalidStateException;
use Varhall\Dbino\Events\SaveArgs;

trait Scope
{
    public static function unscoped()
    {

    }

    /// CONFIGURATION

    protected function scopeDefinition(): array
    {
        throw new InvalidStateException(static::class . '::scopeDefinition() must be overridden');
    }

    protected function initializeScope()
    {
        // register events

        $this->on('saving', function(SaveArgs $args) {
            $definition = $this->scopeDefinition();

            foreach ($definition as $column => $value) {
                $this->$column = $value;
            }
        });

        // register filter functions

        $this->filters[] = function(Selection $collection) {
            $definition = $this->scopeDefinition();

            foreach ($definition as $column => $value) {
                $collection->where($column, $value);
            }
        };
    }
}