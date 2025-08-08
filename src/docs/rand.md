# Rand Documentation

The Rand functionality in FoxQL allows you to easily fetch random data from database tables with support for column selection, WHERE clauses, and table joins.

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

// Get a random user
$randomUser = $db->rand('users', '*');

// Get a random active user
$randomActiveUser = $db->rand('users', '*', [
    'status' => 'active'
]);

// Get a random post with user information
$randomPost = $db->randJoin('posts', [
    '[>]users' => ['user_id' => 'id']
], [
    'posts.id',
    'posts.title',
    'posts.content',
    'users.name(author_name)'
]);
```

## Rand Methods

### rand($table, $columns, $where)

Fetches random data from a table with optional WHERE conditions.

#### Parameters

- `$table` (string): The name of the table to fetch data from
- `$columns` (array|string): The columns to select (use '*' for all columns)
- `$where` (array|null): Optional WHERE conditions to filter the results

#### Return Value

- `array|null`: An array of rows matching the query, or `null` on failure

#### Examples

```php
// Get a random user
$randomUser = $db->rand('users', '*');

// Get a random user with specific columns
$randomUser = $db->rand('users', ['id', 'name', 'email']);

// Get a random active user
$randomUser = $db->rand('users', '*', [
    'status' => 'active'
]);

// Get a random user with complex WHERE conditions
$randomUser = $db->rand('users', '*', [
    'status' => 'active',
    'registration_date[>]' => '2023-01-01',
    'OR' => [
        'role' => ['admin', 'moderator'],
        'login_count[>]' => 10
    ]
]);
```

### randJoin($table, $join, $columns, $where)

Fetches random data from a table with support for table joins.

#### Parameters

- `$table` (string): The name of the main table
- `$join` (array): The join conditions
- `$columns` (array|string): The columns to select
- `$where` (array|null): Optional WHERE conditions

#### Return Value

- `array|null`: An array of rows matching the query, or `null` on failure

#### Examples

```php
// Get a random post with user information
$randomPost = $db->randJoin('posts', [
    '[>]users' => ['user_id' => 'id']
], [
    'posts.id',
    'posts.title',
    'posts.content',
    'users.name(author_name)',
    'users.email(author_email)'
]);

// Get a random post by an active user
$randomPost = $db->randJoin('posts', [
    '[>]users' => ['user_id' => 'id']
], [
    'posts.id',
    'posts.title',
    'posts.content',
    'users.name(author_name)'
], [
    'users.status' => 'active'
]);

// Get a random comment with post and user information
$randomComment = $db->randJoin('comments', [
    '[>]posts' => ['post_id' => 'id'],
    '[>]users' => ['user_id' => 'id']
], [
    'comments.id',
    'comments.content',
    'posts.title(post_title)',
    'users.name(user_name)'
]);
```

### getRand()

Gets the Rand model instance for direct access to its methods.

#### Return Value

- `\FoxQL\Models\Rand`: The Rand model instance

#### Example

```php
// Get the Rand model instance
$randModel = $db->getRand();

// Use the model directly
$randomUser = $randModel->execute('users', '*', [
    'status' => 'active'
]);
```

## Advanced Usage

### Limiting Results

By default, the `rand` and `randJoin` methods return all matching rows in random order. To limit the number of results, use the `LIMIT` clause in the WHERE conditions:

```php
// Get 5 random users
$randomUsers = $db->rand('users', '*', [
    'LIMIT' => 5
]);

// Get 3 random active users
$randomActiveUsers = $db->rand('users', '*', [
    'status' => 'active',
    'LIMIT' => 3
]);
```

### Column Aliases and Data Mapping

```php
// Get a random user with column aliases
$randomUser = $db->rand('users', [
    'id',
    'name(display_name)',
    'email(contact_email)'
]);

// Get a random user with nested data structure
$randomUser = $db->rand('users', [
    'id',
    'name',
    'profile' => [
        'email',
        'phone',
        'address'
    ]
]);
```

### Raw Expressions

```php
// Get a random user with raw SQL expressions
$randomUser = $db->rand('users', [
    'id',
    'name',
    'age' => $db->raw('TIMESTAMPDIFF(YEAR, birthdate, CURDATE())')
]);
```

## Error Handling

The `rand` and `randJoin` methods return `null` if an error occurs. You can check the last error message using the `getError()` method:

```php
$randomUser = $db->rand('non_existent_table', '*');

if ($randomUser === null) {
    echo "Error: " . $db->getError();
}
```