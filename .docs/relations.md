# Relations

- [Introduction](#introduction)
- [Defining Relationships](#defining-relationships)
  - [One To Many](#one-to-many)
  - [Defining The Inverse of The Relationship](#defining-the-inverse-of-the-relationship)
  - [One To One](#one-to-one)
  - [One To One (Inverse) / Belongs To](#one-to-one-inverse)
  - [Has One Through](#has-one-through)
  - [Has Many Through](#has-many-through)
  - [Key Conventions](#has-many-key-conventions)
- [Many To Many Relationships](#many-to-many)
  - [Defining The Inverse of The Relationship](#Defining The Inverse of The Relationship)


<a name="introduction"></a>
## Introduction

Database tables are often related to one another. For example, an author may have many books or an order could be related to the user who placed it. Dbino makes managing and working with these relationships easy, and supports a variety of common relationships:

<a name="defining-relationships"></a>
## Defining Relationships

Relationships are defined as dynamic properties on model classes. Since relationships also serve as powerful collections, defining relationships as methods provides powerful method chaining and querying capabilities. For example, we may chain additional query constraints on this `books` relationship:

    $author->books->where('available', true)->first();

<a name="one-to-many"></a>
### One To Many

A one-to-many relationship is a very basic type of database relationship. For example, a `Author` model might be associated with many `Book` model through `author_id` foreign key column. To define this relationship, we will place an `books` method on the `Author` model. The `books` method should call the `hasMany` method and return its result.

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class Author extends Model
    {
        public function books()
        {
            return $this->hasMany(Book::class, 'author_id');
        }

        protected function table()
        {
            return 'authors';
        }
    }

The first argument passed to the `hasMany` method is the name of the related model class. The second argument is name of column which is the table related through. Of course database index and potentialy foreign key constraint should be defined in the database but it is not necessary. Relations are always bound to the primary key of the related table. Primary key should always be defined. Once the relationship is defined, we may retrieve the related record using dynamic properties. Dynamic properties allow you to access relationship methods as if they were properties defined on the model. The result collection can be used standard way and the included items are standard model instances of `Book` model.

    $books = Author::find(1)->books;

    foreach ($books as $book) {
        // ...
    }

Since all relationships also serve as collections, you may add further constraints to the relationship query by calling the `books` method and continuing to chain conditions onto the query:

    $books = Author::find(1)->books
                        ->where('title LIKE ?', '%PHP%')
                        ->first();


<a name="one-to-one-defining-the-inverse-of-the-relationship"></a>
#### Defining The Inverse Of The Relationship

All the relations can be uni-directional or bi-directional. The direction only depends on dynamic properties.

If backward access from `Book` model to `Author` model is needed, we can define the inverse of a `hasMany` relationship using the `belongsTo` method:

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class Book extends Model
    {
        public function author()
        {
            return $this->belongsTo(Author::class, 'author_id');
        }

        protected function table()
        {
            return 'books';
        }
    }

When invoking the `author` method, `Author` model that has an `id` which matches the `author_id` column on the `Author` model is returned.

<a name="one-to-one"></a>
### One To One

A one-to-one relationship is used to define relationships where a single model depends only on one model. For example, a book single author and author can has only one book. Like all other relationships, one-to-one relationships are defined by defining a dynamic property on model:

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class Author extends Model
    {
        public function book()
        {
            return $this->hasOne(Book::class, 'author_id');
        }

        protected function table()
        {
            return 'authors';
        }
    }

If backward access from `Book` model to `Author` model is needed, we can define the inverse of a `hasOne` relationship using the `belongsTo` method.

> {tip} Remember `hasOne` is something like alternative to `hasMany`. It means that foreign key must be included in related table, it means in `Book` in this case.
> If `Author` referenced to `Book` using `book_id` column, `belongsTo` would be needed.


<a name="one-to-one-inverse"></a>
### One To One (Inverse) / Belongs To

Now that we can access all of a post's comments, let's define a relationship to allow a comment to access its parent post. To define the inverse of a `hasMany` relationship, define a relationship method on the child model which calls the `belongsTo` method:

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class Book extends Model
    {
        public function author()
        {
            return $this->belongsTo(Author::class, 'author_id');
        }

        protected function table()
        {
            return 'books';
        }
    }

Once the relationship has been defined, we can retrieve a book's author by accessing the `author` "dynamic relationship property":

    use App\Models\Book;

    $book = Book::find(1);

    return $book->author->name;

In the example above, Dbino will attempt to find a `Book` model that has an `id` which matches the `id` column on the `Book` model.

<a name="has-many-key-conventions"></a>
#### Key Conventions

Since Nette Database uses database metadata to determine relationships between tables, no special arguments are needed. If foreign keys are properly defined on database columns, column arguments in relationship methods `hasMany`, `hasOne`, `belonsTo` are optional.

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class Author extends Model
    {
        public function books()
        {
            return $this->hasMany(Book::class);
        }

        protected function table()
        {
            return 'authors';
        }
    }

<a name="many-to-many"></a>
## Many To Many Relationships

Many-to-many relations are slightly more complicated than `hasOne` and `hasMany` relationships. An example of a many-to-many relationship is a book that has many tags and those tags are also shared by other books in the application. For example, a book may have assigned the tags of "PHP" and "Advanced"; however, those tags may also be assigned to other books as well. So, a book has many tags and a tag has many books.

<a name="many-to-many-table-structure"></a>
#### Table Structure

To define this relationship, three database tables are needed: `books`, `tags`, and `book_tags`. The `book_tags` table is derived from the alphabetical order of the related model names and contains `user_id` and `role_id` columns. This table is used as an intermediate table linking the books and tags.

Remember, since a role can belong to many books, we cannot simply place a `book_id` column on the `tags` table. This would mean that a tag could only belong to a single book. In order to provide support for tags being assigned to multiple books, the `book_tags` table is needed. We can summarize the relationship's table structure like so:

    books
        id - integer
        name - string

    tags
        id - integer
        name - string

    book_tags
        book_id - integer
        tag_id - integer

<a name="many-to-many-model-structure"></a>
#### Model Structure

Many-to-many relationships are defined by writing a method that returns the result of the `belongsToMany` method. The `belongsToMany` method is provided by the `Illuminate\Database\Eloquent\Model` base class that is used by all of your application's Eloquent models. For example, let's define a `roles` method on our `User` model. The first argument passed to this method is the name of the related model class:

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class Book extends Model
    {
        /**
         * The tags that belong to the book.
         */
        public function tags()
        {
            return $this->belongsToMany(Tag::class, 'book_tags', 'book_id', 'tag_id');
        }
    }

Once the relationship is defined, you may access the user's roles using the `roles` dynamic relationship property:

    use App\Models\Book;

    $book = Book::find(1);

    foreach ($book->tags as $tag) {
        //
    }

Since all relationships also serve as collection, you may add further constraints to the relationship query by calling the `tags` method and continuing to chain conditions onto the query:

    $tags = Book::find(1)->tags()->orderBy('name')->fetch();

<a name="many-to-many-defining-the-inverse-of-the-relationship"></a>
#### Defining The Inverse Of The Relationship

To define the "inverse" of a many-to-many relationship, you should define a method on the related model which also returns the result of the `belongsToMany` method. To complete our book / tag example, let's define the `books` method on the `Tag` model:

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class Tag extends Model
    {
        /**
         * The books that belong to the tag.
         */
        public function books()
        {
            return $this->belongsToMany(Book::class, 'book_tags', 'tag_id', 'book_id');
        }
    }

As you can see, the relationship is defined exactly the same as its `Book` model counterpart with the exception of referencing the `App\Models\Book` model. Since we're reusing the `belongsToMany` method, all of the usual table and key customization options are available when defining the "inverse" of many-to-many relationships.
