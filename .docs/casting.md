# Mutators & Casting

- [Introduction](#introduction)
- [Accessors & Mutators](#accessors-and-mutators)
  - [Defining An Accessor](#defining-an-accessor)
  - [Defining A Mutator](#defining-a-mutator)
- [Attribute Casting](#attribute-casting)
  - [JSON Casting](#json-casting)
- [Custom Casts](#custom-casts)
- [Array / JSON Serialization](#array-json-serialization)
  - [Hiding Attributes From Array](#hiding-attributes-from-array)

<a name="introduction"></a>
## Introduction

Accessors, mutators, and attribute casting allow you to transform Dbino attribute values when you retrieve or set them on model instances. For example, you may want to encrypt a value while it is stored in the database, and then automatically decrypt the attribute when you access it on an Dbino model. Or, you may want to convert a JSON string that is stored in your database to an array when it is accessed via your Dbino model.

<a name="accessors-and-mutators"></a>
## Accessors & Mutators

<a name="defining-an-accessor"></a>
### Defining An Accessor

An accessor transforms an Dbino attribute value when it is accessed. To define an accessor, create a `get{Attribute}Attribute` method on your model where `{Attribute}` is the "studly" cased name of the column you wish to access.

In this example, we'll define an accessor for the `first_name` attribute. The accessor will automatically be called by Dbino when attempting to retrieve the value of the `first_name` attribute:

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class User extends Model
    {
        /**
         * Get the user's first name.
         *
         * @param  string  $value
         * @return string
         */
        public function getFirstNameAttribute($value)
        {
            return ucfirst($value);
        }
    }

As you can see, the original value of the column is passed to the accessor, allowing you to manipulate and return the value. To access the value of the accessor, you may simply access the `first_name` attribute on a model instance:

    use App\Models\User;

    $user = User::find(1);

    $firstName = $user->first_name;

You are not limited to interacting with a single attribute within your accessor. You may also use accessors to return new, computed values from existing attributes:

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

> {tip} Another alternative to current `full_name` example could be use of dynamic attributes

<a name="defining-a-mutator"></a>
### Defining A Mutator

A mutator transforms an Dbino attribute value when it is set. To define a mutator, define a `set{Attribute}Attribute` method on your model where `{Attribute}` is the "studly" cased name of the column you wish to access.

Let's define a mutator for the `first_name` attribute. This mutator will be automatically called when we attempt to set the value of the `first_name` attribute on the model:

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class User extends Model
    {
        /**
         * Set the user's first name.
         *
         * @param  string  $value
         * @return void
         */
        public function setFirstNameAttribute($value)
        {
            $this->attributes['first_name'] = strtolower($value);
        }
    }

The mutator will receive the value that is being set on the attribute, allowing you to manipulate the value and set the manipulated value on the Eloquent model's internal `$attributes` property. To use our mutator, we only need to set the `first_name` attribute on an Eloquent model:

    use App\Models\User;

    $user = User::find(1);

    $user->first_name = 'Hans';

In this example, the `setFirstNameAttribute` function will be called with the value `Hans`. The mutator will then apply the `strtolower` function to the name and set its resulting value in the internal `$attributes` array.

<a name="attribute-casting"></a>
## Attribute Casting

Attribute casting provides functionality similar to accessors and mutators without requiring you to define any additional methods on your model. Instead, your model's `$casts` property provides a convenient method of converting attributes to common data types.

The `$casts` property should be an array where the key is the name of the attribute being cast and the value is the type you wish to cast the column to. The supported cast types are:

- `bool` or `boolean`
- `int` or `integer`
- `double`
- `float` or `number` or `real`
- `string`
- `json`

To demonstrate attribute casting, let's cast the `is_admin` attribute, which is stored in our database as an integer (`0` or `1`) to a boolean value:

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class User extends Model
    {
        /**
         * The attributes that should be cast.
         *
         * @var array
         */
        protected $casts = [
            'is_admin' => 'bool',
        ];
    }

After defining the cast, the `is_admin` attribute will always be cast to a boolean when you access it, even if the underlying value is stored in the database as an integer:

    $user = App\Models\User::find(1);

    if ($user->is_admin) {
        //
    }

<a name="json-casting"></a>
### JSON Casting

The `json` cast is particularly useful when working with columns that are stored as serialized JSON. For example, if your database has a `JSON` or `TEXT` field type that contains serialized JSON, adding the `json` cast to that attribute will automatically deserialize the attribute to a JSON object when you access it on your Dbino model:

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class User extends Model
    {
        /**
         * The attributes that should be cast.
         *
         * @var array
         */
        protected $casts = [
            'options' => 'json',
        ];
    }

Once the cast is defined, you may access the `options` attribute and it will automatically be deserialized from JSON into a PHP array. When you set the value of the `options` attribute, the given array will automatically be serialized back into JSON for storage:

    use App\Models\User;

    $user = User::find(1);
    $options = $user->options;

    $options->invoice_name = 'Hans Ulrich';
    $options->invoice_city = 'Munchen';

    $user->options = $options;
    $user->save();

> TODO: document JSON options

<a name="custom-casts"></a>
## Custom Casts

Dbino has a variety of built-in, helpful cast types; however, you may occasionally need to define your own cast types. You may accomplish this by defining a class that extends the `AttributeCast`.

Inherited classes can override `get` and `set` method. The `get` method is responsible for transforming a raw value from the database into a cast value, while the `set` method should transform a cast value into a raw value that can be stored in the database. As an example, we will re-implement the built-in `json` cast type as a custom cast type:

    <?php

    namespace App\Casts;

    use Varhall\Dbino\Casts\AttributeCast;

    class Json extends AttributeCast
    {
        public function get(Model $model, $property, $value)
        {
            return json_decode($value, true);
        }

        public function set(Model $model, $property, $value)
        {
            return json_encode($value);
        }
    }

Once you have defined a custom cast type, you may attach it to a model attribute using its class name:

    <?php

    namespace App\Models;

    use App\Casts\JsonCast;
    use Varhall\Dbino\Model;

    class User extends Model
    {
        protected $casts = [
            'options' => JsonCast::class,
        ];
    }

Some casts can be shortened using table and code below:

| Cast      | Class               |
| ----------|---------------------|
| bool      | BooleanCast::class  |
| boolean   | BooleanCast::class  |
| int       | IntegerCast::class  |
| integer   | IntegerCast::class  |
| double    | DoubleCast::class   |
| float     | FloatCast::class    |
| real      | FloatCast::class    |
| number    | FloatCast::class    |
| string    | StringCast::class   |
| json      | JsonCast::class     |
| hash      | HashCast::class     |

    <?php

    namespace App\Models;

    use App\Casts\JsonCast;
    use Varhall\Dbino\Model;

    class User extends Model
    {
        protected $casts = [
            'options' => 'json',
        ];
    }

`AttributeCast` also can some arguments. These can be passed using ":" and separated by ",". Or another option is to define them in array. All these arguments are passed as constructor arguments to `AttributeCast` class

    <?php

    namespace App\Models;

    use App\Casts\JsonCast;
    use Varhall\Dbino\Model;

    class User extends Model
    {
        protected $casts = [
            'options' => 'json:nullable,primitive',
            'info'    => [ 'json', 'nullabel', 'primitive' ]
        ];
    }

<a name="array-json-serialization"></a>
### Array / JSON Serialization

When an Dbino model is converted to an array or JSON using the `toArray` and `toJson` methods, your custom cast value objects will typically be serialized as well as long as they implement the `Varhall\Utilino\ISerialiable`.

    <?php

    namespace App\Models;

    use App\Casts\Json;
    use Varhall\Dbino\Model;

    class User extends Model
    {
        public function full_name()
        {
            return "{$this->name} {$this->surname}";
        }

        public function toArray()
        {
            return array_merge(parent::toArray(), [
                'full_name' => $this->full_name
            ]);
        }
    }

Collections are converted recursively using `toArray` method. In case you want to convert only collection to primitive array and not the nested object `asArray` method can be used.

    use App\Models\User;

    $users = User::all();
    $array = $users->asArray();

<a name="hiding-attributes-from-array"></a>
## Hiding Attributes From Array

Sometimes you may wish to limit the attributes, such as passwords, that are included in your model's array or JSON representation. To do so, define a `hiddenAttributes` method to your model. In attributes that are listed in the `hiddenAttributes` array result will not be included in the serialized representation of your model:

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class User extends Model
    {
        protected function hiddenAttributes()
        {
            return [
                'password'
            ];
        }
    }