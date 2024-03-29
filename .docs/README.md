# Dbino

Dbino is Nette Database extension acting as an object-relational mapper (ORM) that makes it enjoyable to interact with your database. When using Dbino, each database table has a corresponding "Model" that is used to interact with that table. In addition to retrieving records from the database table, Dbino models allow you to insert, update, and delete records from the table as well.

> {tip} Before getting started, be sure to have working Nette project and properly configured a database connection in your application's `config/local.neon` configuration file. For more information on configuring your database, check out [the database documentation](https://doc.nette.org/en/3.1/database).

## Setup

Enable Dbino extension in config.neon file. No special configuration is needed since Dbino extends Nette Database library.

    database:
        dsn: 'mysql:host=127.0.0.1;dbname=database'
        user: 'user'
        password: 'pass'

    extensions:
        dbino: Varhall\Dbino\DI\DbinoExtension

## Usage

Define database model class first which is ActiveRow extension

    <?php

    namespace App\Models;

    use Varhall\Dbino\Model;

    class Author extends Model
    {
        /**
         * The table associated with the model.
         */
        protected function table()
        {
            return 'authors';
        }
    }

After the model is defined you can make simple database operation based on Active Record pattern. There are some basic operations.

    use App\Models\Author;

    // Retrieve a model by its primary key...
    $author = Author::find(1);

    // Retrieve the all models
    $authors = Author::all();

    // Retrieve the models matching the query constraints...
    $authors = Author::where('name', 'Hans');

CRUD operations are also very simple.

    // Create a new model instance and save it to the database
    $author = Author::create([
        'name'    => 'Hans',
        'surname' => 'Winkler',
        'enabled' => true
    ]);

    // Update the model in the database
    Author::find(1)->update([
        'name'    => 'Hans',
        'surname' => 'Winkler',
        'enabled' => true
    ]);

    // Delete the model from the database
    Author::find(1)->delete();

# More information

To learn more about Dbino, check out the following topics:

- [Models](models.md) - Defining models and their properties
- [Relations](relations.md) - Defining relations between models
- [Queries](queries.md) - Querying models
- [Casting](casting.md) - Casting model properties
