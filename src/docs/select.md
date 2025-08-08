# Select Documentation

The Select functionality in FoxQL allows you to easily retrieve data from database tables with support for advanced features like column selection, WHERE clauses, table joins, distinct selection, data mapping, and more.

## Basic Usage

```php
// Create a new FoxQL instance
$db = new \FoxQL\FoxQL([
    'type' => 'mysql',
    'database' => 'my_database',
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'password'
]);

// Select all columns from a table
$users = $db->select('users', '*');

// Select specific columns
$users = $db->select('users', ['id', 'name', 'email']);

// Select with WHERE conditions
$activeUsers = $db->select('users', ['id', 'name', 'email'], [
    'status' => 'active',
    'age[>]' => 18
]);
```

## Select Methods

### select($table, $columns, $where)

Selects data from a table with optional WHERE conditions.

#### Parameters

- `$table` (string): The name of the table to select data from
- `$columns` (array|string): The columns to select (use '*' for all columns)
- `$where` (array|null): Optional WHERE conditions to filter the results

#### Return Value

- `array|null`: An array of rows matching the query, or `null` on failure

#### Examples

```php
// Select all columns from users table
$users = $db->select('users', '*');

// Select specific columns
$users = $db->select('users', ['id', 'name', 'email']);

// Select with WHERE conditions
$users = $db->select('users', ['id', 'name', 'email'], [
    'status' => 'active',
    'created_at[>]' => '2023-01-01'
]);
```

### selectJoin($table, $join, $columns, $where)

Selects data from a table with support for table joins.

#### Parameters

- `$table` (string): The name of the main table
- `$join` (array): The join conditions
- `$columns` (array|string): The columns to select
- `$where` (array|null): Optional WHERE conditions

#### Return Value

- `array|null`: An array of rows matching the query, or `null` on failure

#### Examples

```php
// Join users and posts tables
$userPosts = $db->selectJoin('users', [
    'posts' => ['INNER', 'users.id = posts.user_id']
], [
    'users.id',
    'users.name',
    'posts.title',
    'posts.content'
], [
    'users.status' => 'active'
]);
```

### selectOne($table, $columns, $where)

Selects a single row from a table.

#### Parameters

- `$table` (string): The name of the table
- `$columns` (array|string): The columns to select
- `$where` (array|null): Optional WHERE conditions

#### Return Value

- `array|null`: The first row matching the query, or `null` if not found or on failure

#### Examples

```php
// Get a user by ID
$user = $db->selectOne('users', '*', ['id' => 1]);

// Get a user by email with specific columns
$user = $db->selectOne('users', ['id', 'name', 'email'], ['email' => 'john@example.com']);
```

### selectValue($table, $column, $where)

Selects a single value from a table.

#### Parameters

- `$table` (string): The name of the table
- `$column` (string): The column to select
- `$where` (array|null): Optional WHERE conditions

#### Return Value

- `mixed|null`: The value of the specified column from the first matching row, or `null` if not found or on failure

#### Examples

```php
// Get a user's name by ID
$name = $db->selectValue('users', 'name', ['id' => 1]);

// Count active users
$count = $db->selectValue('users', 'COUNT(*)', ['status' => 'active']);
```

### selectCallback($table, $columns, $where, $callback)

Selects data from a table and processes each row with a callback function.

#### Parameters

- `$table` (string): The name of the table
- `$columns` (array|string): The columns to select
- `$where` (array|null): Optional WHERE conditions
- `$callback` (callable): The callback function to execute for each row

#### Return Value

- `bool`: Whether the operation was successful

#### Examples

```php
// Process each user row
$db->selectCallback('users', '*', ['status' => 'active'], function($user) {
    echo "Processing user: {$user['name']}\n";
    // Do something with the user data
});
```

## Advanced Features

### WHERE Clause Operators

The WHERE clause supports various operators for filtering data:

```php
$db->select('users', '*', [
    'id' => 1,                     // id = 1
    'name[!]' => 'John',          // name != 'John'
    'age[>]' => 18,               // age > 18
    'age[>=]' => 21,              // age >= 21
    'age[<]' => 65,               // age < 65
    'age[<=]' => 60,              // age <= 60
    'status[<>]' => 'pending',    // status <> 'pending'
    'email[LIKE]' => '%gmail.com', // email LIKE '%gmail.com'
    'role[NOT LIKE]' => 'admin%', // role NOT LIKE 'admin%'
    'id[IN]' => [1, 2, 3],        // id IN (1, 2, 3)
    'id[NOT IN]' => [4, 5],       // id NOT IN (4, 5)
    'created[BETWEEN]' => ['2023-01-01', '2023-12-31'], // created BETWEEN '2023-01-01' AND '2023-12-31'
    'updated[NOT BETWEEN]' => ['2022-01-01', '2022-12-31'] // updated NOT BETWEEN '2022-01-01' AND '2022-12-31'
]);
```

### Distinct Selection

To select distinct values, prefix the column name with `@`:

```php
// Select distinct roles
$roles = $db->select('users', ['@role']);
```

### Table Joining

Join multiple tables with various join types:

```php
$data = $db->selectJoin('users', [
    'posts' => ['INNER', 'users.id = posts.user_id'],
    'comments' => ['LEFT', 'posts.id = comments.post_id']
], [
    'users.id',
    'users.name',
    'posts.title',
    'comments.content'
]);
```

### Column Aliases

Use aliases for columns:

```php
$users = $db->select('users', [
    'user_id' => 'id',
    'full_name' => 'name',
    'contact' => 'email'
]);
```

### Raw SQL Expressions

Use raw SQL expressions in queries:

```php
$users = $db->select('users', [
    'id',
    'name',
    'age',
    'age_group' => $db->raw('CASE WHEN age < 18 THEN "minor" WHEN age >= 18 AND age < 65 THEN "adult" ELSE "senior" END')
]);
```

### Data Mapping

Customize output data structure:

```php
$users = $db->select('users', [
    'user_info' => [
        'id',
        'name',
        'email'
    ],
    'stats' => [
        'posts_count' => $db->raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id)')
    ]
]);
```

## Direct Access to the Select Model

You can access the Select model directly for more advanced operations:

```php
$select = $db->getSelect();

// Execute a select query
$users = $select->execute('users', ['id', 'name', 'email'], ['status' => 'active']);

// Get the last PDO statement
$statement = $select->getStatement();

// Check for errors
$error = $select->getError();
```