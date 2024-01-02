<?php

namespace Varhall\Dbino;

use Nette\Database\Table\ActiveRow;
use Varhall\Dbino\Casts\AttributeCast;
use Varhall\Dbino\Collections\Collection;
use Varhall\Dbino\Collections\GroupedCollection;
use Varhall\Dbino\Collections\ManyToManySelection;
use Varhall\Dbino\Events\DeleteArgs;
use Varhall\Dbino\Events\InsertArgs;
use Varhall\Dbino\Events\UpdateArgs;
use Varhall\Dbino\Scopes\Scope;
use Varhall\Dbino\Traits\Events;
use Varhall\Dbino\Traits\SoftDeletes;
use Varhall\Dbino\Traits\Timestamps;
use Varhall\Utilino\Collections\ICollection;
use Varhall\Utilino\ISerializable;
use Varhall\Utilino\Utils\Reflection;

/**
 * Base database model class
 *
 * @method static Collection all()
 * @method static Collection where($condition, ...$parameters)
 * @method static $this find($id)
 * @method static $this findOrDefault($id, array $data = [])
 * @method static $this findOrFail($id)
 * @method static instance(array $data = [])
 * @method static create(array $data = [])
 * @method static array columns()
 * @method static Collection withTrashed()
 * @method static Collection onlyTrashed()
 */
abstract class Model implements ISerializable
{
    use Events {
        raise as private raise_Events;
    }

    private Dbino $dbino;

    private ?ActiveRow $row;

    protected array $attributes     = [];

    protected $casts                = [];

    protected $scopes               = [];


    ////////////////////////////////////////////////////////////////////////////
    /// MAGIC METHODS                                                        ///
    ////////////////////////////////////////////////////////////////////////////

    public function __construct(Dbino $dbino, ?ActiveRow $row = null)
    {
        $this->dbino = $dbino;
        $this->row = $row;

        $this->on('creating', function($args) { $this->raise('saving', $args); });
        $this->on('updating', function($args) { $this->raise('saving', $args); });

        $this->on('created', function($args) { $this->raise('saved', $args); });
        $this->on('updated', function($args) { $this->raise('saved', $args); });

        $this->initializeTraits();
        $this->setup();
    }

    public function __isset(string $name): bool
    {
        if (isset($this->attributes[$name])) {
            return true;
        }

        if (!$this->isSaved()) {
            return false;
        }

        return isset($this->row[$name]);
    }

    public function &__get(string $key): mixed
    {
        // call method with attribute name if exists
        if (method_exists($this, $key)) {
            $value = call_user_func([$this, $key]);
            return $value;
        }

        $camelKey = $this->toCamelCase($key);

        // call camel case method with attribute name if exists
        if (method_exists($this, $camelKey)) {
            $value = call_user_func([$this, $camelKey]);
            return $value;
        }

        $value = $this->getAttribute($key);

        // call mutator getter if exists
        $mutator = 'get' . ucfirst($key) . 'Attribute';
        if (method_exists($this, $mutator)) {
            $value = call_user_func([$this, $mutator], $value);
            return $value;
        }

        return $value;
    }

    public function __set(string $name, mixed $value): void
    {
        $camelName = $this->toCamelCase($name);

        // call mutator getter if exists
        $mutator = 'set' . ucfirst($camelName) . 'Attribute';
        if (method_exists($this, $mutator)) {
            call_user_func([$this, $mutator], $value);

            // set value
        } else {
            $this->setAttribute($name, $value);
        }
    }

    public static function __callStatic(string $name, array $arguments): mixed
    {
        $repository = static::configuration()->getRepository();

        if (!method_exists($repository, $name)) {
            throw new \Nette\MemberAccessException("Method {$name} not found in class " . get_class($repository));
        }

        return call_user_func_array([ $repository, $name ], $arguments);
    }



    ////////////////////////////////////////////////////////////////////////////
    /// STATIC METHODS                                                       ///
    ////////////////////////////////////////////////////////////////////////////
    
    public static function configuration(): Configuration
    {
        return (new static(Dbino::instance()))->getConfiguration();
    }


    ////////////////////////////////////////////////////////////////////////////
    /// CONFIG METHODS                                                       ///
    ////////////////////////////////////////////////////////////////////////////

    protected abstract function table();

    protected function repository(): string
    {
        return Repository::class;
    }

    protected function setup()
    {

    }

    protected function defaults()
    {
        return [];
    }

    protected function hiddenAttributes()
    {
        return [];
    }

    protected function serializationDateFormat()
    {
        return 'c';
    }

    public function addScope(Scope $scope, ?string $name = null): void
    {
        $this->scopes[$name ?? get_class($scope)] = $scope;
    }

    public function removeScope(string $name): void
    {
        unset($this->scopes[$name]);
    }


    ////////////////////////////////////////////////////////////////////////////
    /// PUBLIC INSTANCE METHODS                                              ///
    ////////////////////////////////////////////////////////////////////////////

    public function fill(array $data): static
    {
        $this->setAttributes($data);

        return $this;
    }

    public function isSaved(): bool
    {
        return !$this->isNew() && empty($this->attributes);
    }

    public function isNew(): bool
    {
        return !$this->row;
    }

    public function save(): static
    {
        if (empty($this->attributes)) {
            return $this;
        }

        if ($this->isNew()) {
            $this->insertInstance();

        } else {
            $this->updateInstance();
        }

        $this->attributes = [];

        return $this;
    }

    public function update(iterable $data): bool
    {
        $this->setAttributes($data);

        return $this->updateInstance();
    }

    public function delete(): int
    {
        if ($this->isNew()) {
            throw new \Nette\InvalidStateException('Cannot delete unsaved model');
        }

        $args = new DeleteArgs([
            'id'        => $this->row->getPrimary(),
            'instance'  => $this,
            'soft'      => false
        ]);

        $this->raise('deleting', $args);
        $result = $this->row->delete();
        $this->raise('deleted', $args);

        return $result;
    }

    public function duplicate(array $values = [], array $except = []): static
    {
        if ($this->isNew()) {
            throw new \Nette\InvalidStateException('Cannot duplicate unsaved model');
        }

        $except = array_merge(
            Reflection::hasTrait($this, Timestamps::class) ? array_values($this->timestampsColumns()) : [],
            Reflection::hasTrait($this, SoftDeletes::class) ? (array) $this->softDeleteColumn : [],
            (array) $this->row->getTable()->getPrimary(false),
            $except
        );


        $data = $this->toNativeArray();

        foreach ($except as $property) {
            if (isset($data[$property])) {
                unset($data[$property]);
            }
        }

        return static::create(array_merge($data, $values));
    }

    public function getPrimary(): mixed
    {
        return $this->row ? $this->row->getPrimary() : null;
    }

    public function toArray(): array
    {
        $values = $this->toNativeArray();

        foreach ($values as $key => $value) {
            $values[$key] = $this->readField($key, $value);

            if ($value instanceof \DateTime)
                $values[$key] = $value->format($this->serializationDateFormat());

            if (in_array($key, $this->hiddenAttributes()))
                unset($values[$key]);

            if ($value instanceof \Varhall\Utilino\ISerializable) {
                $values[$key] = $value->toArray();
            }
        }

        return $values;
    }

    public function toJson()
    {
        return \Nette\Utils\Json::encode($this->toArray());
    }


    ////////////////////////////////////////////////////////////////////////////
    /// PRIVATE & PROTECTED METHODS                                          ///
    ////////////////////////////////////////////////////////////////////////////

    protected function insertInstance(): void
    {
        // raise events
        $this->raise('creating', new InsertArgs([
            'data' => $this->attributes,
            'instance' => $this
        ]));

        // insert
        $model = $this->getConfiguration()->getRepository()->all()->insert($this->prepareDbData($this->attributes));

        if ($model instanceof Model) {
            $this->row = $model->row;
        }

        // raise events
        $this->raise('created', new InsertArgs([
            'data'      => $this->attributes,
            'instance'  => $this,
            'id'        => $this->row ? $this->row->getPrimary() : null
        ]));
    }

    protected function updateInstance()
    {
        // prcompute data
        $originals = $this->row->toArray();
        $diff = array_filter($this->attributes, function($value, $key) use ($originals) {
            return $value != $originals[$key];
        }, ARRAY_FILTER_USE_BOTH);

        // raise events
        $args = new UpdateArgs([
            'data'      => $this->toNativeArray(),
            'instance'  => $this,
            'id'        => $this->row->getPrimary(),
            'diff'      => $diff
        ]);

        $this->raise('updating', $args);

        // update
        $result = $this->row->update($this->prepareDbData($this->attributes));

        // raise events
        $this->raise('updated', $args);

        return $result;
    }

    protected function hasMany(string $class, ?string $throughColumn = null): ICollection
    {
        $configuration = $class::configuration();
        $related = $this->row->related($configuration->table, $throughColumn);

        return new GroupedCollection($related, $configuration);
    }

    protected function hasOne(string $class, ?string $throughColumn = null): Model
    {
        return $this->hasMany($class, $throughColumn)->first();
    }

    protected function belongsTo(string $class, ?string $throughColumn = null): ?Model
    {
        if ($this->isNew()) {
            return $this->dbino->repository($class)->all()->get($this->$throughColumn);
        }

        $table = $class::configuration()->table;
        $ref = $this->row->ref($table, $throughColumn);

        if (!$ref) {
            return null;
        }

        return new $class($this->dbino, $ref);
    }

    /**
     * Maps many to many relationship. Example reference from Student (current) to Course (related):
     *
     * @param string $class Target class Course::class
     * @param string $intermediateTable Intermediate table student_courses
     * @param string $foreignColumn Column in intermediate table pointing to current class (student_id)
     * @param string $referenceColumn Column in intermediate table pointing to related class (course_id)
     */
    protected function belongsToMany(string $class, string $intermediateTable, string $foreignColumn, string $referenceColumn): ICollection
    {
        $configuration = $class::configuration();

        $intermediate = $this->row->related($intermediateTable, $foreignColumn);
        $result = new ManyToManySelection($intermediate, $intermediateTable, $configuration->table, $foreignColumn, $referenceColumn, $this->row->getPrimary());

        return new GroupedCollection($result, $configuration);
    }

    protected function addCast(string $field, AttributeCast $cast): void
    {
        $this->casts[$field] = $cast;
    }

    protected function initializeTraits(): void
    {
        $class = new \ReflectionClass($this);

        foreach ($class->getTraits() as $trait) {
            $method = 'initialize' . $trait->getShortName();

            if (method_exists($this, $method)) {
                call_user_func([ $this, $method ]);
            }
        }
    }

    protected function getAttribute(string $key): mixed
    {
        // get unsaved value if set
        if (array_key_exists($key, $this->attributes)) {
            return $this->readField($key, $this->attributes[$key]);
        }

        // call parent getter
        $value = null;

        try {
            if (!$this->isNew()) {
                $value = $this->row->$key;
            }

        } catch (\Nette\MemberAccessException $ex) {
            $this->row->accessColumn(null);
            $value = $this->row->$key;
        }

        return $this->readField($key, $value);
    }

    protected function setAttribute(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    protected function setAttributes(iterable $data): void
    {
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
    }

    protected function toNativeArray(): array
    {
        $values = array_merge(!$this->isNew() ? $this->row->toArray() : [], $this->attributes);

        if (!$values || !is_array($values)) {
            throw new \Nette\InvalidStateException('Unable convert row to array');
        }

        return $values;
    }

    protected function readField(string $key, mixed $value): mixed
    {
        $cast = $this->getCast($key);

        $defaults = array_key_exists($key, $this->defaults()) ? $this->defaults()[$key] : null;
        $value = $value ?? $defaults;

        return $cast ? $cast->get($this, $key, $value, [ 'defaults' => $defaults ]) : $value;
    }

    protected function toCamelCase(string $input): string
    {
        $case = implode('',
            array_map(
                function($item) { return ucfirst(strtolower($item)); },
                explode('_', $input)
            )
        );

        return lcfirst($case);
    }

    protected function fromCamelCase(string $input): string
    {
        $input = ucfirst($input);

        preg_match_all('/([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)/', $input, $matches);

        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return strtolower(implode('_', $ret));
    }

    protected function raise(string $event, ...$args): void
    {
        $this->raise_Events($event, ...$args);
        Reflection::callPrivateMethod($this->getConfiguration()->getRepository(), 'raise', [ $event, ...$args ]);
    }

    protected function getCast(string $field): ?AttributeCast
    {
        return isset($this->casts[$field]) ? $this->dbino->cast($this->casts[$field]) : null;
    }

    protected function prepareDbData(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $cast = $this->getCast($key);
            $result[$key] = $cast ? $cast->set($this, $key, $value) : $value;
        }

        return $result;
    }
    
    protected function getConfiguration(): Configuration
    {
        return new Configuration($this->dbino, [
            'model'         => static::class,
            'table'         => $this->table(),
            'repository'    => $this->repository(),
            'casts'         => $this->casts,
            'scopes'        => $this->scopes,
            'events'        => $this->events,
        ]);
    }
}
