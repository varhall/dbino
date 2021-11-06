# Queries

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
## Basic selections

Dbino uses special Collections based on `\Varhall\Utilino\ICollection` interfaces which extends `\Nette\Datatabase\Selection`. This means that all the query functions and techniques from `\Nette\Database\Selection` can be used when building your queries.

Imagine our test class `Author`

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class Author extends Model
    {
        protected function table()
        {
            return 'authors';
        }
    }

Simple function `all` can be used to retrieve all the items from the database.

    $authors = Author::all();

    foreach (authors as $author) {
        echo $author->name;
    }

Using `\Nette\Database\Selection` methods, our current result can be filtered afterwards.

    $authors = Author::all()
                ->where('name', 'John')
                ->where('age > ?', 30)
                ->order('age DESC')
                ->limit(10);

For more detailed explanations about querying see [Nette Database Documentation](https://doc.nette.org/en/3.1/database-explorer#toc-selections).

We are very often required to get database items based on some condition. Finding all the items which fit required condition can be simplified to:

    $authors = Author::where('name', 'John');

## Repositories

Remember, all the static methods on models are fiction. Almost every static model method is relocated to proper `\Varhall\Dbino\Repository` class. Repositories are standard Nette DI services, so they have to be registered in DI Container to be used.

Model repository can be customized to define you own selection methods. Each model class uses `\Varhall\Dbino\Repository` by default. To specify custom repository simply override `repository` method in model definition.

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;
    use App\Repositories\AuthorRepository;

    class Author extends Model
    {
        protected function repository()
        {
            return AuthorRepository::class;
        }

        protected function table()
        {
            return 'authors';
        }
    }
