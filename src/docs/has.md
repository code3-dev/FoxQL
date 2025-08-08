# Has Documentation

The Has functionality in FoxQL allows you to easily check if records exist in database tables with support for WHERE clauses and table joins.

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

// Check if a user exists by ID
$exists = $db->has('users', [
    'id' => 1
]);

// Check if any active users exist
$hasActiveUsers = $db->has('users', [
    'status' => 'active'
]);

// Check if a user has any posts
$hasUserPosts = $db->hasJoin('users', [
    '[>]posts' => ['id' => 'user_id']
], [
    'users.id' => 1
]);
```

## Has Methods

### has($table, $where)

Checks if records exist in a table based on the given WHERE conditions.

#### Parameters

- `$table` (string): The name of the table to check
- `$where` (array): The WHERE clause to filter records

#### Return Value

- `bool`: `true` if records exist, `false` otherwise

#### Examples

```php
// Check if a user exists by ID
$exists = $db->has('users', [
    'id' => 1
]);

// Check if any users match complex conditions
$exists = $db->has('users', [
    'status' => 'active',
    'last_login[>]' => '2023-01-01',
    'OR' => [
        'role' => 'admin',
        'permissions[~]' => '%edit%'
    ]
]);

// Check if any users are female
$hasFemaleUsers = $db->has('users', [
    'gender' => 'female'
]);

if ($hasFemaleUsers) {
    echo "We have female users.";
} else {
    echo "We don't have any female users.";
}
```

### hasJoin($table, $join, $where)

Checks if records exist in a table with support for table joins.

#### Parameters

- `$table` (string): The name of the main table
- `$join` (array): The join conditions
- `$where` (array): The WHERE clause to filter records

#### Return Value

- `bool`: `true` if records exist, `false` otherwise

#### Examples

```php
// Check if a user has any posts
$hasUserPosts = $db->hasJoin('users', [
    '[>]posts' => ['id' => 'user_id']
], [
    'users.id' => 1
]);

// Check if there are any posts by active users
$hasActivePosts = $db->hasJoin('posts', [
    '[>]users' => ['user_id' => 'id']
], [
    'users.status' => 'active'
]);

// Check if there are any comments on posts by a specific user
$hasComments = $db->hasJoin('comments', [
    '[>]posts' => ['post_id' => 'id'],
    '[>]users' => ['posts.user_id' => 'id']
], [
    'users.id' => 1
]);

if ($hasComments) {
    echo "User has comments on their posts.";
} else {
    echo "User has no comments on their posts.";
}
```

### getHas()

Gets the Has model instance for direct access to its methods.

#### Return Value

- `\FoxQL\Models\Has`: The Has model instance

#### Example

```php
// Get the Has model instance
$hasModel = $db->getHas();

// Use the model directly
$exists = $hasModel->execute('users', [
    'id' => 1
]);
```

## Advanced Usage

### Combining with Other Methods

The Has functionality is often used in combination with other methods to check for existence before performing operations:

```php
// Check if a user exists before updating
if ($db->has('users', ['id' => $userId])) {
    $db->update('users', [
        'last_login' => date('Y-m-d H:i:s')
    ], [
        'id' => $userId
    ]);
} else {
    echo "User not found.";
}

// Check if a record exists before getting it
if ($db->has('products', ['sku' => $sku])) {
    $product = $db->get('products', '*', ['sku' => $sku]);
    // Process product...
} else {
    echo "Product not found.";
}
```

### Complex Conditions

```php
// Check for records with complex conditions
$hasSpecialUsers = $db->has('users', [
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

## Error Handling

The `has` and `hasJoin` methods return `false` if an error occurs. You can check the last error message using the `getError()` method:

```php
$exists = $db->has('non_existent_table', [
    'id' => 1
]);

if (!$exists) {
    echo "Error or no records found: " . $db->getError();
}
```