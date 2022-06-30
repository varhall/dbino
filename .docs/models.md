# Models

- [Model Definition](#model-definition)
  - [Property accessors](#property-accessors)
  - [Dynamic properties](#dynamic-properties)
  - [Timestamps](#timestamps)
  - [Default Attribute Values](#default-attribute-values)
- [Retrieving Models](#retrieving-models)
  - [Building Queries](#building-queries)
  - [Collections](#collections)
  - [Advanced Subqueries](#advanced-subqueries)
- [Retrieving Single Models / Aggregates](#retrieving-models)
- [Non-existent Models](#non-existent-models)
- [Inserting & Updating Models](#inserting-and-updating-models)
  - [Inserts](#inserts)
  - [Updates](#updates)
  - [Mass Updates](#mass-updates)
  - [Examining Attribute Changes](#examining-attribute-changes)
- [Deleting Models](#deleting-models)
  - [Deleting Models Using Queries](#deleting-models-using-queries)
  - [Soft Deleting](#soft-deleting)
  - [Configuring Soft Deleted models](#configuring-soft-deleted-models)
  - [Permanently Deleting Models](#permanently-deleting-models)
  - [Restoring Soft Deleted Models](#restoring-soft-deleted-models)
- [Duplicating Models](#duplicating-models)
- [Events](#events)
  - [Model Events](#model-events)
  - [Repository Events](#repository-events)
  - [Custom Events](#custom-events)

<a name="model-definition"></a>
## Model Definition

Model is a simple class automatically mapped to database table. It's a simple extension of `\Nette\Database\Table\ActiveRow`. Let's examine a basic model class:

Each model extends a base class `Model` and defines `table` method which is the class mapped to. Columns and keys are created dynamically from the database. Names are based on column names.

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class Author extends Model
    {
        /**
         * The table associated with the model.
         *
         * @var string
         */
        protected function table()
        {
            return 'authors';
        }
    }

<a name="property-accessors"></a>
### Property accessors

Since the columns are generated automatically using the column names, accessor methods can still be used. This allows the model class to be much more type-hinted and flexible. 

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class Author extends Model
    {
        public function getName()
        {
            return $this->name;
        }
        
        public function setName($value)
        {
            $this->name = $value;
        }

        protected function table()
        {
            return 'authors';
        }
    }

<a name="dynamic-properties"></a>
### Dynamic properties

Models allows to create some dynamic properties. These properties act as readonly virtual columns.

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class Author extends Model
    {
        public function full_name()
        {
            return "{$this->name} {$this->surname}";
        }

        protected function table()
        {
            return 'authors';
        }
    }

After that the dynamic property can be used as standard property returning a value from the method.

    <?php

    $fullname = $author->full_name;
    echo $fullname;   // prints e.g. Martin Hauser 

<a name="timestamps"></a>
### Timestamps

Dbino can automatically manage created and updated timestamps on model. If `Timestamps` trait is used, columns `created_at` and `updated_at` are expected to exist on corresponding database table.  The values are automatically set when models are created or updated.

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;
    use Varhall\Dbino\Traits\Timestamps;

    class Author extends Model
    {
        use Timestamps;

        protected function table()
        {
            return 'authors';
        }
    }

If you need to customize the names of the columns used to store the timestamps, you may define `CREATED_AT` and `UPDATED_AT` constants on your model:

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;
    use Varhall\Dbino\Traits\Timestamps;

    class Author extends Model
    {
        use Timestamps;

        const CREATED_AT = 'creation_date';
        const UPDATED_AT = 'updated_date';

        protected function table()
        {
            return 'authors';
        }
    }


<a name="default-attribute-values"></a>
### Default Attribute Values

By default, a newly instantiated model instance will not contain any attribute values. If you would like to define the default values for some of your model's attributes, you may define a `defaults()` method on your model:

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class Author extends Model
    {
        /**
         * The model's default values for attributes.
         *
         * @var array
         */
        protected function defaults()
        {
            return [
                'allowed' => true,
            ];
        }

        protected function table()
        {
            return 'authors';
        }
    }


<a name="retrieving-models"></a>
## Retrieving Models

Once you have created a model and its associated database table, you are ready to start retrieving data from your database. You can think of each Model as a powerful query builder allowing you to fluently query the database table associated with the model. The model's `all` method will retrieve all of the records from the model's associated database table:

    use App\Models\Author;

    foreach (Author::all() as $author) {
        echo $author->name;
    }

<a name="building-queries"></a>
#### Building Queries

The `all` method returns instance of `Collection`. Collection is improved Nette `Selection` object so you can use all of its query methods and much more!

    $authors = Author::where('published', true)
                   ->where('created_at > ?', new \DateTime('-1 month'))  
                   ->order('name')
                   ->limit(10);

<a name="collections"></a>
### Collections

As we have seen, Collection methods like `all` and `where` retrieve multiple records from the database. However, these methods don't return a plain PHP array. Instead, an instance of `Varhall\Dbino\Collections\Collection` is returned.

The Dbino `Collection` class extends Nette base `Nette\Database\Table\Selection` class and implements `Varhall\Utilino\Collections\ICollection`, which provides a variety of helpful methods for interacting with data collections. For example, the `map` method can transform each item of collection to another value:

    $authors = Author::where('published', true)
                   ->map(function($author) {
                       return $author->name;                    
                   });


In addition to the methods provided by Utilino collection interface, the Dbino collection class provides a few extra methods coming from Nette `Selection`.

Since all of Utilino collections implement PHP's iterable interfaces, you may loop over collections as if they were an array:


    foreach ($authors as $author) {
        echo $author->name;
    }

<a name="advanced-subqueries"></a>
### Advanced Subqueries

> TODO: Document quering using Nette Selection

<a name="retrieving-single-models"></a>
## Retrieving Single Models / Aggregates

In addition to retrieving all of the records matching a given query, you may also retrieve single records using the `find`, `first`, or `firstWhere` methods. Instead of returning a collection of models, these methods return a single model instance:

    use App\Models\Author;

    // Retrieve a model by its primary key...
    $author = Author::find(1);

    // Retrieve the first model matching the query constraints...
    $author = Author::where('name', 'Hans')->first();

Sometimes you may wish to retrieve the first result of a query. The `get` method will return the first result matching the query.

    $author = Flight::where('enabled', true)->limit(1)->get();

<a name="non-existent-models"></a>
#### Non-existent Models

Sometimes you may wish to throw an exception if a model is not found. The `findOrFail` method will retrieve the first result of the query; however, if no result is found, an `Nette\InvalidArgumentException` will be thrown:

    $author = Author::findOrFail(1);

In cases where a model is not found it is possible to create a new model with default data. The `findOrDefault` method does not create new record in the database, only creates an unsaved instance.

    $author = Author::findOrDefault(1, [
        'name'    => 'Hans',
        'surname' => 'Winkler',
        'enabled' => true
    ]);


<a name="inserting-and-updating-models"></a>
## Inserting & Updating Models

A fresh unsaved model instance can be simply created using `instance` method.

    $author = Author::instance([
        'name'    => 'Hans',
        'surname' => 'Winkler',
        'enabled' => true
    ]);

<a name="inserts"></a>
### Inserts

Making database changes is very simple in Dbino. To insert a new record into the database, you should instantiate a new model and set attributes on the model. Then, call the `save` method on the model instance:

    $author = Author::instance([
        'name'    => 'Hans',
        'surname' => 'Winkler',
        'enabled' => true
    ]);

    $author->save();

In this example, model properties are assigned to an empty instance of `App\Models\Author`. When we call the `save` method, a record will be inserted into the database. The model's `created_at` and `updated_at` timestamps will automatically be set when the `save` method is called, so there is no need to set them manually, if model uses `Timestamps` trait.

Alternatively, you may use the `create` method to "save" a new model using a single PHP statement. This is just a shorthand to `instance` and `save` methods. The inserted model instance will be returned to you by the `create` method:

    $author = Author::create([
        'name'    => 'Hans',
        'surname' => 'Winkler',
        'enabled' => true
    ]);

<a name="updates"></a>
### Updates

The `save` method may also be used to update models that already exist in the database. To update a model, you should retrieve it and set any attributes you wish to update. Then, you should call the model's `save` method. Again, the `updated_at` timestamp will automatically be updated, so there is no need to manually set its value:

    $author = Author::find(1);

    $author->name = 'Johann';

    $author->save();

Multiple properties can be automatically assigned using `fill` method.

    $author = Author::find(1);

    $author->fill([
        'name'    => 'Johann',
        'surname' => 'Steiner'
    ]);

    $author->save();

<a name="mass-updates"></a>
#### Mass Updates

Updates can also be performed against models that match a given query. In this example, all authors that are `enabled` will be disabled:

    Author::where('enabled', true)->update([ 'enabled' => false ]);

The `update` method expects an array of column and value pairs representing the columns that should be updated. This is one of standard funcionality of Nette Database.

> {note} When issuing a mass update via Dbino, the `saving`, `saved`, `updating`, and `updated` model events will not be fired for the updated models. Also Timestamps will not be updated. This is because the models are never actually retrieved when issuing a mass update.

<a name="examining-attribute-changes"></a>
#### Examining Attribute Changes

There are `isNew`, `isSaved` provided, to examine the internal state of your model and determine how its attributes have changed from when the model was originally retrieved.

    $author = Author::create([
        'name'    => 'Hans',
        'surname' => 'Winkler',
        'enabled' => true
    ]);

    $author->isNew();     // true
    $author->isSaved();   // false

    $author->save();

    $author->isNew();     // false
    $author->isSaved();   // true

    $author->name = 'Johann';

    $author->isNew();     // false
    $author->isSaved();   // false

    $author->save();

    $author->isNew();     // false
    $author->isSaved();   // true

> TODO: create isDirty($property) method

> TODO: create reset() to reset all unsaved changes

<a name="deleting-models"></a>
## Deleting Models

To delete a model, you may call the `delete` method on the model instance:

    $author = Author::find(1);

    $author->delete();

<a name="deleting-models-using-queries"></a>
#### Deleting Models Using Queries

Of course, you may build an Nette Database query to delete all models matching your query's criteria. In this example, we will delete all flights that are marked as inactive. Like mass updates, mass deletes will not dispatch model events for the models that are deleted:

    $deletedRows = Author::where('enabled', false)->delete();

> {note} When executing a mass delete statement via Nette Database, the `deleting` and `deleted` model events will not be dispatched for the deleted models. This is because the models are never actually retrieved when executing the delete statement.

<a name="soft-deleting"></a>
### Soft Deleting

In addition to actually removing records from your database, models can use `SoftDeletes` trait. When models are soft deleted, they are not actually removed from your database. Instead, a `deleted_at` attribute is set on the model indicating the date and time at which the model was "deleted".

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;
    use Varhall\Dbino\Traits\Timestamps;
    use Varhall\Dbino\Traits\SoftDeletes;

    class Author extends Model
    {
        use Timestamps;
        use SoftDeletes;

        protected function table()
        {
            return 'authors';
        }
    }

> {tip} The `SoftDeletes` trait will automatically cast the `deleted_at` attribute to a `DateTime` instance for you.

Now, when you call the `delete` method on the model, the `deleted_at` column will be set to the current date and time. However, the model's database record will be left in the table. When querying a model that uses soft deletes, the soft deleted models will automatically be excluded from all query results.

To determine if a given model instance has been soft deleted, you may use the `isTrashed` method, which is available only on "soft delete" models.

    if ($author->isTrashed()) {
        //
    }

<a name="configuring-soft-deleted-models"></a>
#### Configuring Soft Deleted Models

Soft delete column can be configured overriding method `softDeleteColumn` defined on model.

    namespace App\Models;

    use Varhall\Dbino\Model;
    use Varhall\Dbino\Traits\Timestamps;
    use Varhall\Dbino\Traits\SoftDeletes;

    class Author extends Model
    {
        use Timestamps;
        use SoftDeletes;

        protected function softDeleteColumn()
        {
            return 'removed_at';
        }

        protected function table()
        {
            return 'authors';
        }
    }


<a name="restoring-soft-deleted-models"></a>
#### Restoring Soft Deleted Models

Sometimes you may wish to "un-delete" a soft deleted model. To restore a soft deleted model, you may call the `restore` method on a model instance. The `restore` method will set the model's `deleted_at` column to `null`:

    $author->restore();


<a name="permanently-deleting-models"></a>
#### Permanently Deleting Models

Sometimes you may need to truly remove a model from your database. You may use the `forceDelete` method to permanently remove a soft deleted model from the database table:

    $author->forceDelete();

<a name="querying-soft-deleted-models"></a>
### Querying Soft Deleted Models

<a name="including-soft-deleted-models"></a>
#### Including Soft Deleted Models

As noted above, soft deleted models will automatically be excluded from query results. However, you may force soft deleted models to be included in a query's results by calling the `withTrashed` method on the query:

    $authors = Author::withTrashed();

<a name="retrieving-only-soft-deleted-models"></a>
#### Retrieving Only Soft Deleted Models

The `onlyTrashed` method will retrieve **only** soft deleted models:

    $authors = Author::onlyTrashed();

<a name="duplicating-models"></a>
## Duplicating Models

You may create a copy of an existing model instance using the `duplicate` method. This method is particularly useful when you have model instances that share many of the same attributes. The clone is already saved in database.

    use App\Models\Address;

    $author = Author::create([
        'name'    => 'Hans',
        'surname' => 'Winkler',
        'enabled' => true
    ]);

    $clone = $author->duplicate();

Values can be automatically prefilled or excluded from created clone.

    use App\Models\Address;

    $author = Author::create([
        'name'    => 'Hans',
        'surname' => 'Winkler',
        'enabled' => true
    ]);

    $clone = $author->duplicate([ 'name' => 'Johann' ], [ 'enabled' ]);

<a name="events"></a>
## Events

Dbino models dispatch several events, allowing you to hook into the following moments in a model's lifecycle: `creating`, `created`, `updating`, `updated`, `saving`, `saved`, `deleting`, `deleted`, `restoring`, `restored`.

When a new model is saved for the first time, the `creating` and `created` events will dispatch. The `updating` / `updated` events will dispatch when an existing model is modified and the `save` method is called. The `saving` / `saved` events will dispatch when a model is created or updated - even if the model's attributes have not been changed.

Events can be handled on both Model and Repository classes.

<a name="model-events"></a>
### Model Events

To start listening to model events, register event handler using on method. This handler is bound to current instance only. But using setup method it can be registered globally.

    namespace App\Models;

    use Varhall\Dbino\Model;
    use Varhall\Dbino\Events\UpdateArgs;

    class Author extends Model
    {
        protected function setup()
        {
            $this->on('updated', function(UpdateArgs $args) {
                // ...
            });
        }

        protected function table()
        {
            return 'authors';
        }
    }

<a name="repository-events"></a>
### Repository events

For some cases Repository events can be very useful. The main reason for Repository events to be used is possibility to call methods on DI services.

All of the default triggered events are exactly the same as with Model Events.

    namespace App\Repositories;

    use Varhall\Dbino\Repository;
    use Varhall\Dbino\Events\UpdateArgs;

    class AuthorsRepository extends Repository
    {
        protected $rabbit;

        public function __construct(RabbitMQ $rabbit)
        {
            $this->rabbit = $rabbit;

            // Raise RabbitMQ event anytime the Author is updated

            $this->on('updated', function(UpdateArgs $args) {
                $this->rabbit->push('authors', $args->instance->toArray());
            });
        }
    }

<a name="custom-events"></a>
### Custom events

The predefined events are not the only one. You can raise and handle any event you want. The usage is same for Model and Repository events.

    namespace App\Repositories;

    use Varhall\Dbino\Repository;
    use Varhall\Dbino\Events\UpdateArgs;

    class AuthorsRepository extends Repository
    {
        public function __construct()
        {
            $this->on('custom_event', function($args) {
                // ...
            });
        }

        public function findByCategory($category)
        {
            // ...

            $this->raise('custom_event', $category);
        }
    }
