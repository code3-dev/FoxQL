# Get Documentation

The Get functionality in FoxQL allows you to easily retrieve a single record from database tables with support for advanced features like column selection, WHERE clauses, table joins, data mapping, and more.

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

// Get a single user by ID
$user = $db->get('users', '*', [
    'id' => 1
]);

// Get a single user with specific columns
$user = $db->get('users', ['id', 'name', 'email'], [
    'status' => 'active'
]);

// Get a user with join to posts table
$userWithPosts = $db->getJoin('users', [
    '[>]posts' => ['id' => 'user_id']
], [
    'users.id',
    'users.name',
    'posts.title',
    'posts.content'
], [
    'users.id' => 1
]);
```

## Get Methods

### get($table, $columns, $where)

Retrieves a single record from a table with optional WHERE conditions.

#### Parameters

- `$table` (string): The name of the table to get data from
- `$columns` (array|string): The columns to select (use '*' for all columns)
- `$where` (array|null): Optional WHERE conditions to filter the results

#### Return Value

- `mixed`: The record data as an associative array, or `null` if no record is found

#### Examples

```php
// Get a user by ID
$user = $db->get('users', '*', [
    'id' => 1
]);

// Get a user with specific columns
$user = $db->get('users', ['id', 'name', 'email'], [
    'email' => 'john@example.com'
]);

// Get a user with complex WHERE conditions
$user = $db->get('users', '*', [
    'status' => 'active',
    'last_login[>]' => '2023-01-01',
    'OR' => [
        'role' => 'admin',
        'permissions[~]' => '%edit%'
    ]
]);
```

### getJoin($table, $join, $columns, $where)

Retrieves a single record from a table with support for table joins.

#### Parameters

- `$table` (string): The name of the main table
- `$join` (array): The join conditions
- `$columns` (array|string): The columns to select
- `$where` (array|null): Optional WHERE conditions

#### Return Value

- `mixed`: The record data as an associative array, or `null` if no record is found

#### Examples

```php
// Get a user with their latest post
$userWithPost = $db->getJoin('users', [
    '[>]posts' => ['id' => 'user_id']
], [
    'users.id',
    'users.name',
    'users.email',
    'posts.title',
    'posts.content'
], [
    'users.id' => 1,
    'ORDER' => ['posts.created_at' => 'DESC'],
    'LIMIT' => 1
]);

// Get a post with user information
$postWithUser = $db->getJoin('posts', [
    '[>]users' => ['user_id' => 'id']
], [
    'posts.id',
    'posts.title',
    'posts.content',
    'users.name(author_name)',
    'users.email(author_email)'
], [
    'posts.id' => 5
]);
```

### getGet()

Gets the Get model instance for direct access to its methods.

#### Return Value

- `\FoxQL\Models\Get`: The Get model instance

#### Example

```php
// Get the Get model instance
$getModel = $db->getGet();

// Use the model directly
$user = $getModel->execute('users', '*', [
    'id' => 1
]);
```

## Advanced Usage

### Column Aliases

```php
// Get a user with column aliases
$user = $db->get('users', [
    'id',
    'name(display_name)',
    'email(contact_email)'
], [
    'id' => 1
]);

// Result: ['id' => 1, 'display_name' => 'John Doe', 'contact_email' => 'john@example.com']
```

### Data Mapping

```php
// Get a user with nested data structure
$user = $db->get('users', [
    'id',
    'name',
    'profile' => [
        'email',
        'phone',
        'address'
    ]
], [
    'id' => 1
]);

// Result: ['id' => 1, 'name' => 'John Doe', 'profile' => ['email' => 'john@example.com', 'phone' => '123456789', 'address' => '123 Main St']]
```

### Raw Expressions

```php
// Get a user with raw SQL expressions
$user = $db->get('users', [
    'id',
    'name',
    'age' => $db->raw('TIMESTAMPDIFF(YEAR, birthdate, CURDATE())')
], [
    'id' => 1
]);
```

## Error Handling

The `get` and `getJoin` methods return `null` if no record is found or if an error occurs. You can check the last error message using the `getError()` method:

```php
$user = $db->get('users', '*', [
    'id' => 999 // Non-existent ID
]);

if ($user === null) {
    echo "Error or no user found: " . $db->getError();
}
```