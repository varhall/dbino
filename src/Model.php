<?php

namespace Varhall\Dbino;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Varhall\Dbino\Casts\AttributeCast;
use Varhall\Dbino\Collections\Collection;
use Varhall\Dbino\Collections\GroupedCollection;
use Varhall\Dbino\Collections\ManyToManyCollection;
use Varhall\Dbino\Events\DeleteArgs;
use Varhall\Dbino\Events\InsertArgs;
use Varhall\Dbino\Events\UpdateArgs;
use Varhall\Dbino\Mutators\Mutator;
use Varhall\Dbino\Traits\SoftDeletes;
use Varhall\Dbino\Traits\Timestamps;
use Varhall\Utilino\ISerializable;
use Varhall\Utilino\Utils\Reflection;

/**
 * Base database model class
 *
 * @author Ondrej Sibrava <sibrava@varhall.cz>
 *
 * @method static Collection all()
 * @method static Collection where($condition, ...$parameters)
 * @method static $this find($id)
 * @method static $this findOrDefault($id, array $data = [])
 * @method static $this findOrFail($id)
 * @method static instance(array $data)
 * @method static create(array $data)
 * @method static array columns()
 * @method static Collection withTrashed()
 * @method static Collection onlyTrashed()
 */
abstract class Model extends ActiveRow implements ISerializable
{
    /** @var array */
    protected $attributes   = [];

    /** @var array */
    protected $events       = [];

    /** @var array */
    protected $casts        = [];


    ////////////////////////////////////////////////////////////////////////////
    /// MAGIC METHODS                                                        ///
    ////////////////////////////////////////////////////////////////////////////

    public function __construct()
    {
        $this->on('creating', function($args) { $this->raise('saving', $args); });
        $this->on('updating', function($args) { $this->raise('saving', $args); });

        $this->on('created', function($args) { $this->raise('saved', $args); });
        $this->on('updated', function($args) { $this->raise('saved', $args); });

        $this->initializeTraits();
        $this->registerPlugins();
        $this->setup();
    }

    public function __isset($name)
    {
        if (isset($this->attributes[$name]))
            return true;

        if (!$this->isSaved())
            return false;

        return parent::__isset($name);
    }

    public function &__get(string $key)
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

    public function __set($name, $value)
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

    public static function __callStatic($name, $arguments)
    {
        $repository = static::getRepository();

        if (!method_exists($repository, $name))
            throw new \Nette\MemberAccessException("Method {$name} not found in class " . get_class($repository));

        return call_user_func_array([ $repository, $name ], $arguments);
    }



    ////////////////////////////////////////////////////////////////////////////
    /// STATIC METHODS                                                       ///
    ////////////////////////////////////////////////////////////////////////////

    public static function getRepository(): Repository
    {
        return Dbino::_repository(static::class);
    }
    
    public static function search(Selection $collection, $value, array $args = [])
    {
        $columns = static::instance()->searchedColumns();

        if (empty($columns))
            return $collection;

        $query = [];
        $params = [];
        foreach ($columns as $column) {
            $query[] = "{$column} LIKE ?";
            $params[] = "%{$value}%";
        }

        if (!empty($args)) {
            call_user_func_array('array_push', array_merge([&$query], array_keys($args)));
            call_user_func_array('array_push', array_merge([&$params], array_values($args)));
        }

        return $collection->whereOr(array_combine($query, $params));
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

    protected function plugins()
    {
        return [];
    }

    protected function defaults()
    {
        return [];
    }

    protected function attributeTypes()
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

    protected function searchedColumns()
    {
        return [];
    }



    ////////////////////////////////////////////////////////////////////////////
    /// PUBLIC INSTANCE METHODS                                              ///
    ////////////////////////////////////////////////////////////////////////////

    public function fill(array $data)
    {
        $this->setAttributes($data);

        return $this;
    }

    public function isSaved()
    {
        return !$this->isNew() && empty($this->attributes);
    }

    public function isNew()
    {
        try {
            return !$this->getTable();

        } catch (\TypeError $ex) {
            return true;
        }
    }

    public function save()
    {
        if (empty($this->attributes))
            return $this;

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
        $args = new DeleteArgs([
            'id'        => $this->getPrimary(),
            'instance'  => $this,
            'soft'      => false
        ]);

        $this->raise('deleting', $args);

        $result = parent::delete();

        $this->raise('deleted', $args);

        return $result;
    }

    public function duplicate(array $values = [], array $except = [])
    {
        $except = array_merge(
            Reflection::hasTrait($this, Timestamps::class) ? array_values($this->timestampsColumns()) : [],
            Reflection::hasTrait($this, SoftDeletes::class) ? (array) $this->softDeleteColumn() : [],
            (array) $this->getTable()->getPrimary(false),
            $except
        );

        $data = $this->toNativeArray();

        foreach ($except as $property) {
            if (isset($data[$property]))
                unset($data[$property]);
        }

        return static::create(array_merge($data, $values));
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

    public function on($event, callable $callback)
    {
        $this->events[$event][] = $callback;
    }



    ////////////////////////////////////////////////////////////////////////////
    /// PRIVATE & PROTECTED METHODS                                          ///
    ////////////////////////////////////////////////////////////////////////////

    protected function insertInstance()
    {
        // raise events
        $this->raise('creating', new InsertArgs([
            'data' => $this->attributes,
            'instance' => $this
        ]));

        // insert
        $model = static::getRepository()->all()->insert($this->prepareDbData($this->attributes));
        Reflection::writePrivateProperty($this, 'data', Reflection::readPrivateProperty($model, 'data'));
        Reflection::writePrivateProperty($this, 'table', Reflection::readPrivateProperty($model, 'table'));

        // raise events
        $this->raise('created', new InsertArgs([
            'data'      => $this->attributes,
            'instance'  => $model,
            'id'        => $model instanceof ActiveRow ? $model->getPrimary() : null
        ]));
    }

    protected function updateInstance()
    {
        // prcompute data
        $originals = parent::toArray();
        $diff = array_filter($this->attributes, function($value, $key) use ($originals) {
            return $value != $originals[$key];
        }, ARRAY_FILTER_USE_BOTH);

        // raise events
        $args = new UpdateArgs([
            'data'      => $this->toNativeArray(),
            'instance'  => $this,
            'id'        => $this->getPrimary(),
            'diff'      => $diff
        ]);

        $this->raise('updating', $args);

        // update
        $result = parent::update($this->prepareDbData($this->attributes));

        // raise events
        $this->raise('updated', $args);

        return $result;
    }

    protected function hasMany($class, $throughColumn = null)
    {
        $table = Dbino::_config($class, 'table');
        $related = $this->related($table, $throughColumn);

        return new GroupedCollection($related, $class);
    }

    protected function hasOne($class, $throughColumn = null)
    {
        return $this->hasMany($class, $throughColumn)->first();
    }

    protected function belongsTo($class, $throughColumn = null)
    {
        $table = Dbino::_config($class, 'table');

        if ($this->isNew())
            return Dbino::_repository($class)->all()->get($this->$throughColumn);

        $ref = $this->ref($table, $throughColumn);

        if (!$ref)
            return null;

        $instance = Dbino::_model($class);

        Reflection::writePrivateProperty($instance, 'data', Reflection::readPrivateProperty($ref, 'data'));
        Reflection::writePrivateProperty($instance, 'table', Reflection::readPrivateProperty($ref, 'table'));

        return $instance;
    }

    protected function belongsToMany($class, $intermediateTable, $foreignColumn, $referenceColumn)
    {
        $table = Dbino::_config($class, 'table');

        $intermediate = $this->related($intermediateTable, $foreignColumn);
        return new ManyToManyCollection($intermediate, $intermediateTable, $table, $foreignColumn, $referenceColumn, $this->getPrimary(), $class);
    }

    protected function registerPlugins()
    {
        foreach ($this->plugins() as $plugin) {
            $plugin->register($this);
        }
    }

    protected function addCast($field, AttributeCast $cast)
    {
        $this->casts[$field] = $cast;
    }

    protected function initializeTraits()
    {
        $class = new \ReflectionClass($this);

        foreach ($class->getTraits() as $trait) {
            $method = 'initialize' . $trait->getShortName();

            if (method_exists($this, $method))
                call_user_func([ $this, $method ]);
        }
    }

    protected function getAttribute($key)
    {
        // get unsaved value if set
        if (array_key_exists($key, $this->attributes))
            return $this->readField($key, $this->attributes[$key]);

        // call parent getter
        $value = null;

        try {
            if (!$this->isNew())
                $value = parent::__get($key);

        } catch (\Nette\MemberAccessException $ex) {
            $this->accessColumn(null);
            $value = parent::__get($key);
        }

        return $this->readField($key, $value);
    }

    protected function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    protected function setAttributes($data)
    {
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
    }

    protected function toNativeArray()
    {
        $values = array_merge(!$this->isNew() ? parent::toArray() : [], $this->attributes);

        if (!$values || !is_array($values)) {
            throw new \Nette\InvalidStateException('Unable convert row to array');
        }

        return $values;
    }

    protected function readField($key, $value)
    {
        $cast = $this->getCast($key);

        $defaults = array_key_exists($key, $this->defaults()) ? $this->defaults()[$key] : null;
        $value = $value ?? $defaults;

        return $cast ? $cast->get($this, $key, $value, [ 'defaults' => $defaults ]) : $value;
    }

    protected function toCamelCase($input)
    {
        $case = implode('',
            array_map(
                function($item) { return ucfirst(strtolower($item)); },
                explode('_', $input)
            )
        );

        return lcfirst($case);
    }

    protected function fromCamelCase($input)
    {
        $input = ucfirst($input);

        preg_match_all('/([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)/', $input, $matches);

        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return strtolower(implode('_', $ret));
    }

    protected function raise($event, ...$args)
    {
        if (!isset($this->events[$event]))
            return;

        foreach ($this->events[$event] as $handler) {
            if (!is_callable($handler))
                continue;

            call_user_func_array($handler, $args);
        }
    }

    protected function getCast($field): ?AttributeCast
    {
        return isset($this->casts[$field]) ? Dbino::_cast($this->casts[$field]) : null;
    }

    protected function prepareDbData(array $data)
    {
        $result = [];

        foreach ($data as $key => $value) {
            $cast = $this->getCast($key);
            $result[$key] = $cast ? $cast->set($this, $key, $value) : $value;
        }

        return $result;
    }
}
