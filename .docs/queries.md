# Queries

[comment]: <> generate navigation links from <a> elements and headings like navigation above

- [Basic selections](#basic-selections)
- [Repositories](#repositories)
  - [Custom repository methods](#custom-repository-methods)
  - [Custom query where scopes](#custom-query-where-scopes)
  - [Conditional where scopes](#conditional-where-scopes)


<a name="basic-selections"></a>
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

You can use all the features from ICollection to improve your queries. For example, you can use `map` and `every` methods:

    $predicate = Author::all()
                ->where('name', 'John')
                ->map(fn($a) => $a->name)
                ->every(fn($n) => strlen($n) > 5);

But remember, ICollection methods are programmatic. They are run after the SQL data is fetched. It can result in some performance drawbacks.

<a name="repositories"></a>
## Repositories

You've read this millionth times, static is hell, and we partially think so too. Remember, all the static methods on models are fiction. Almost every static model method is relocated to proper `\Varhall\Dbino\Repository` class. Repositories are standard Nette DI services, so they have to be registered in DI Container to be used.

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

<a name="custom-repository-methods"></a>
### Custom repository methods

Custom repository methods can be defined in repository class. For example, we can define `findByName` method in `AuthorRepository` class.

    <?php

    namespace App\Repositories;

    use Varhall\Dbino\Repository;
    use Varhall\Dbino\Collections\Collection;

    class AuthorRepository extends Repository
    {
        public function findByName(string $name): Collection
        {
            return $this->all()->where('name', $name);
        }
    }

Now we can use our custom method in our code.

    $authors = Author::findByName('John');

<a name="custom-query-where-scopes"></a>
### Custom query where scopes

You can also define custom query scopes in your model class. For example, we can define `whereAvailable` method in `AuthorRepository` class.
The scope method must be in the form of `where{ScopeName}`.

    <?php

    namespace App\Repositories;

    use Varhall\Dbino\Repository;
    use Varhall\Dbino\Collections\Collection;

    class AuthorRepository extends Repository
    {
        public function whereEnabled(Collection $collection, bool $value = true): Collection
        {
            return $collection->where('enabled', $value);
        }
    }

Now we can use our custom method in our code.

    $authors = Author::all()
                ->where('name', 'John')
                ->whereEnabled();

<a name="conditional-where-scopes"></a>
### Conditional where scopes

For someone, it can be more comfortable to write method chains but dealing with conditional where clauses is not so possible.
The solution is to use `whereIf` method which accepts boolean as first argument and other arguments are same as in standard `where` function.
The condition is simply ignored if the first argument is false.

    $isActiveFilterEnabled = true;
    $authors = Author::all()
                ->where('name', 'John')
                ->whereIf($isActiveFilterEnabled, 'enabled', true);

You can use same way to define conditional where with your custom where scopes e.g.

    $isActiveFilterEnabled = true;
    $authors = Author::all()
                ->where('name', 'John')
                ->whereEnabledIf($isActiveFilterEnabled);
