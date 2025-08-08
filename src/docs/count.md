# Count Documentation

The Count functionality in FoxQL allows you to easily count the number of rows in database tables with support for WHERE clauses and table joins.

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

// Count all users
$totalUsers = $db->count('users');

// Count active users
$activeUsers = $db->count('users', [
    'status' => 'active'
]);

// Count posts by a specific user
$userPosts = $db->count('posts', [
    'user_id' => 1
]);

// Count posts by female users
$femalePosts = $db->countJoin('posts', [
    '[>]users' => ['user_id' => 'id']
], 'posts.id', [
    'users.gender' => 'female'
]);
```

## Count Methods

### count($table, $where)

Counts the number of rows in a table with optional WHERE conditions.

#### Parameters

- `$table` (string): The name of the table to count rows from
- `$where` (array|null): Optional WHERE conditions to filter the results

#### Return Value

- `int`: The number of rows

#### Examples

```php
// Count all users
$totalUsers = $db->count('users');

// Count active users
$activeUsers = $db->count('users', [
    'status' => 'active'
]);

// Count users with complex WHERE conditions
$specialUsers = $db->count('users', [
    'status' => 'active',
    'registration_date[>]' => '2023-01-01',
    'OR' => [
        'role' => ['admin', 'moderator'],
        'login_count[>]' => 10
    ]
]);

// Display the count
echo "We have {$activeUsers} active users.";
```

### countJoin($table, $join, $column, $where)

Counts the number of rows in a table with support for table joins.

#### Parameters

- `$table` (string): The name of the main table
- `$join` (array): The join conditions
- `$column` (string): The target column to be counted
- `$where` (array|null): Optional WHERE conditions

#### Return Value

- `int`: The number of rows

#### Examples

```php
// Count posts by female users
$femalePosts = $db->countJoin('posts', [
    '[>]users' => ['user_id' => 'id']
], 'posts.id', [
    'users.gender' => 'female'
]);

// Count distinct users who have posts
$usersWithPosts = $db->countJoin('users', [
    '[>]posts' => ['id' => 'user_id']
], 'DISTINCT users.id');

// Count comments on posts by a specific user
$commentCount = $db->countJoin('comments', [
    '[>]posts' => ['post_id' => 'id'],
    '[>]users' => ['posts.user_id' => 'id']
], 'comments.id', [
    'users.id' => 1
]);

// Display the count
echo "There are {$femalePosts} posts by female users.";
```

### getCount()

Gets the Count model instance for direct access to its methods.

#### Return Value

- `\FoxQL\Models\Count`: The Count model instance

#### Example

```php
// Get the Count model instance
$countModel = $db->getCount();

// Use the model directly
$totalUsers = $countModel->execute('users');
```

## Advanced Usage

### Counting Distinct Values

To count distinct values, use the `DISTINCT` keyword in the column parameter of the `countJoin` method:

```php
// Count distinct users who have posts
$usersWithPosts = $db->countJoin('users', [
    '[>]posts' => ['id' => 'user_id']
], 'DISTINCT users.id');

// Count distinct categories used in posts
$usedCategories = $db->countJoin('categories', [
    '[>]post_categories' => ['id' => 'category_id'],
    '[>]posts' => ['post_categories.post_id' => 'id']
], 'DISTINCT categories.id');
```

### Complex Conditions

```php
// Count users with complex conditions
$specialUsers = $db->count('users', [
    'registration_date[<>]' => ['2023-01-01', '2023-12-31'],
    'status' => 'active',
    'login_count[>]' => 10,
    'OR' => [
        'role' => ['admin', 'moderator'],
        'AND' => [
            'subscription' => 'premium',
            'payment_status' => 'completed'
        ]
    ]
]);
```

### Using Raw Expressions

```php
// Count users registered this month using raw SQL
$thisMonthUsers = $db->count('users', [
    'registration_date[~]' => $db->raw('DATE_FORMAT(NOW(), "%Y-%m-%%")')
]);
```

## Error Handling

The `count` and `countJoin` methods return `0` if an error occurs. You can check the last error message using the `getError()` method:

```php
$count = $db->count('non_existent_table');

if ($count === 0) {
    echo "Error or no records: " . $db->getError();
}
```