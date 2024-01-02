<?php

namespace Varhall\Dbino\Traits;

use Nette\InvalidStateException;
use Varhall\Dbino\Events\SaveArgs;
use Varhall\Dbino\Scopes\ColumnScope;

trait Scope
{
    public static function unscoped(string|array $columns)
    {
        return static::withoutScope(array_map(fn($column) => "scope.{$column}", (array) $columns));
    }

    /// CONFIGURATION

    protected function scopeDefinition(): array
    {
        throw new InvalidStateException(static::class . '::scopeDefinition() must be overridden');
    }

    protected function initializeScope()
    {
        // initialize filters
        foreach ($this->scopeDefinition() as $column => $value) {
            $this->addScope(new ColumnScope($column, $value), "scope.{$column}");
        }

        // initialize events
        $this->on('saving', function(SaveArgs $args) {
            $definition = $this->scopeDefinition();

            foreach ($definition as $column => $value) {
                $this->$column = $value;
            }
        });
    }
}