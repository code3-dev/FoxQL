# FoxQL Migration Builder

The Migration Builder provides a fluent interface for creating, altering, and dropping database tables in FoxQL. It's inspired by Laravel's migration system and supports all database types that FoxQL supports (MySQL, PostgreSQL, SQLite, Sybase, Oracle, MSSQL).

## Basic Usage

### Creating Tables

To create a new table, use the `createTable` method on the FoxQL instance:

```php
$db->createTable('users', function($table) {
    $table->increments('id');
    $table->string('name', 100)->notNull();
    $table->string('email', 100)->notNull()->unique();
    $table->timestamps(); // Adds created_at and updated_at columns
});
```

### Altering Tables

To alter an existing table, use the `alterTable` method:

```php
$db->alterTable('users', function($table) {
    $table->string('username', 50)->after('name');
    $table->string('phone', 20)->nullable();
});
```

### Renaming Tables

To rename a table, use the `renameTable` method:

```php
$db->renameTable('posts', 'articles');
```

### Dropping Tables

To drop a table, use the `dropTable` method:

```php
$db->dropTable('articles');
```

## Column Types

The Migration Builder supports a wide range of column types:

| Method | Description |
|--------|-------------|
| `increments(name)` | Auto-incrementing ID (primary key) |
| `bigIncrements(name)` | Auto-incrementing BIGINT (primary key) |
| `char(name, length)` | CHAR column |
| `string(name, length)` | VARCHAR column |
| `text(name)` | TEXT column |
| `mediumText(name)` | MEDIUMTEXT column |
| `longText(name)` | LONGTEXT column |
| `integer(name)` | INTEGER column |
| `tinyInteger(name)` | TINYINT column |
| `smallInteger(name)` | SMALLINT column |
| `mediumInteger(name)` | MEDIUMINT column |
| `bigInteger(name)` | BIGINT column |
| `float(name, precision, scale)` | FLOAT column |
| `double(name, precision, scale)` | DOUBLE column |
| `decimal(name, precision, scale)` | DECIMAL column |
| `boolean(name)` | BOOLEAN column |
| `enum(name, values)` | ENUM column |
| `json(name)` | JSON column |
| `date(name)` | DATE column |
| `dateTime(name)` | DATETIME column |
| `timestamp(name)` | TIMESTAMP column |
| `time(name)` | TIME column |
| `binary(name)` | BINARY column |
| `uuid(name)` | UUID column |
| `ipAddress(name)` | IP address column |
| `macAddress(name)` | MAC address column |

## Column Modifiers

Column modifiers can be chained to column definitions:

```php
$table->string('email')->notNull()->unique()->comment('User email address');
```

Available modifiers:

| Modifier | Description |
|----------|-------------|
| `notNull()` | Column does not allow NULL values |
| `nullable()` | Column allows NULL values |
| `default(value)` | Set a default value for the column |
| `unsigned()` | Set INTEGER columns as UNSIGNED |
| `primary()` | Add a primary key constraint |
| `unique()` | Add a unique constraint |
| `index()` | Add an index |
| `check(constraint)` | Add a check constraint |
| `comment(text)` | Add a comment to the column |
| `autoIncrement()` | Set column as auto-incrementing |
| `first()` | Place the column at the first position |
| `after(column)` | Place the column after another column |
| `references(table, column)` | Add a foreign key constraint |
| `useCurrent()` | Use CURRENT_TIMESTAMP as default value |
| `useCurrentOnUpdate()` | Use CURRENT_TIMESTAMP on update |
| `collation(collation)` | Set the collation for the column |
| `charset(charset)` | Set the character set for the column |
| `storedAsJson()` | Store the column as JSON |

## Indexes and Keys

You can add various types of indexes and keys to your tables:

```php
$table->primary('id'); // Add a primary key
$table->primary(['id', 'type']); // Composite primary key

$table->unique('email'); // Add a unique index
$table->unique(['email', 'username']); // Composite unique index

$table->index('status'); // Add a regular index
$table->index(['status', 'created_at']); // Composite index

// Add a foreign key
$table->foreign('user_id', 'users', 'id', 'fk_posts_user_id', 'CASCADE', 'CASCADE');
```

## Special Column Types

### Timestamps

Add `created_at` and `updated_at` columns:

```php
$table->timestamps();
```

### Soft Deletes

Add a `deleted_at` column for soft deletes:

```php
$table->softDeletes();
```

## Table Options

You can set various table options:

```php
$table->engine('InnoDB'); // Set the storage engine (MySQL)
$table->charset('utf8mb4'); // Set the character set
$table->collation('utf8mb4_unicode_ci'); // Set the collation
$table->comment('This table stores user data'); // Add a table comment
```

## Migrations

The Migration Builder also supports a Laravel-like migration system:

### Creating Migration Files

Create a migration file with `up` and `down` methods:

```php
<?php

namespace Migrations;

use FoxQL\Models\Migration;

class CreateUsersTable
{
    /**
     * Run the migration.
     *
     * @param \FoxQL\Models\Migration $migration
     * @return void
     */
    public function up(Migration $migration): void
    {
        $migration->createTable('users', function($table) {
            $table->increments('id');
            $table->string('name', 100)->notNull();
            $table->string('email', 100)->notNull()->unique();
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migration.
     *
     * @param \FoxQL\Models\Migration $migration
     * @return void
     */
    public function down(Migration $migration): void
    {
        $migration->dropTable('users');
    }
}
```

### Running Migrations

To run all pending migrations:

```php
$db->migrate('/path/to/migrations');
```

### Rolling Back Migrations

To roll back the last batch of migrations:

```php
$db->rollbackMigrations('/path/to/migrations');
```

### Resetting Migrations

To roll back all migrations:

```php
$db->reset('/path/to/migrations');
```

### Refreshing Migrations

To roll back all migrations and run them again:

```php
$db->refresh('/path/to/migrations');
```

## Database-Specific Features

The Migration Builder automatically adapts to the specific database type you're using:

- **MySQL/MariaDB**: Uses backticks for identifiers, supports ENGINE, CHARSET, and COLLATION options
- **PostgreSQL**: Uses double quotes for identifiers, supports SERIAL for auto-increment
- **SQLite**: Uses double quotes for identifiers, has limited ALTER TABLE support
- **Sybase**: Uses double quotes for identifiers
- **Oracle**: Uses double quotes for identifiers, has specific syntax for sequences
- **MSSQL**: Uses square brackets for identifiers, supports IDENTITY for auto-increment

## Example

See the complete example in `examples/migration_example.php`.