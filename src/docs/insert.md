# Insert Documentation

The Insert functionality in FoxQL allows you to easily insert data into database tables with support for advanced features like array serialization, type auto-detection, multi-insertion, PDOStatement access, and SQL functions.

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

// Insert data into a table
$db->insert('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'created_at' => date('Y-m-d H:i:s')
]);

// Get the last inserted ID
$lastId = $db->getInsert()->lastInsertId();
```

## Insert Method

The `insert` method allows you to insert data into a table.

### Parameters

- `$table` (string): The name of the table to insert data into
- `$data` (array): An associative array of column names and values, or an array of arrays for multi-insertion
- `$returnStatement` (bool): Whether to return the PDOStatement instead of row count (default: false)

### Return Value

- `int|\PDOStatement|null`: The number of affected rows, PDOStatement (if `$returnStatement` is true), or `null` on failure

### Examples

#### Basic Insert

```php
$result = $db->insert('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30,
    'active' => 1
]);
```

#### Getting the Last Insert ID

```php
$db->insert('users', [
    'name' => 'Jane Smith',
    'email' => 'jane@example.com'
]);

$lastId = $db->getInsert()->lastInsertId();
echo "Last insert ID: {$lastId}\n";
```

#### Array Serialization

```php
// Arrays are automatically serialized to JSON
$db->insert('users', [
    'name' => 'User with preferences',
    'email' => 'user@example.com',
    'preferences' => ['theme' => 'dark', 'notifications' => true]
]);
```

#### Using SQL Functions

```php
// Use raw SQL expressions for functions
$db->insert('users', [
    'name' => 'User with timestamp',
    'email' => 'timestamp@example.com',
    'created_at' => $db->raw('CURRENT_TIMESTAMP')
]);
```

#### Multi-insertion (Multiple Rows at Once)

```php
// Insert multiple rows in a single query
$db->insert('users', [
    [
        'name' => 'Batch User 1',
        'email' => 'batch1@example.com'
    ],
    [
        'name' => 'Batch User 2',
        'email' => 'batch2@example.com'
    ],
    [
        'name' => 'Batch User 3',
        'email' => 'batch3@example.com'
    ]
]);
```

#### Getting the PDOStatement

```php
// Get the PDOStatement for further processing
$statement = $db->insert('users', [
    'name' => 'Statement User',
    'email' => 'statement@example.com'
], true);

if ($statement) {
    echo "Rows affected: {$statement->rowCount()}\n";
    // You can use other PDOStatement methods here
}
```
```php
if ($result !== null) {
    echo "Insert successful! Affected rows: {$result}\n";
} else {
    echo "Insert failed: " . $db->getError() . "\n";
}
```

## Advanced Features

### Type Auto-detection

The Insert model automatically handles different data types:

- **Arrays**: Automatically serialized to JSON
- **JsonSerializable objects**: Converted to JSON
- **DateTime objects**: Formatted as 'Y-m-d H:i:s'
- **Boolean values**: Converted to integers (1 or 0)
- **Null values**: Preserved as NULL in the database

```php
// Examples of type auto-detection
$db->insert('users', [
    'name' => 'Type Test User',
    'preferences' => ['theme' => 'light', 'language' => 'en'], // Array to JSON
    'is_active' => true, // Boolean to integer (1)
    'last_login' => new DateTime(), // DateTime to string
    'manager_id' => null // NULL preserved
]);
```

### Raw SQL Expressions

You can use raw SQL expressions for database functions, calculations, or other SQL features:

```php
$db->insert('posts', [
    'title' => 'New Post',
    'content' => 'Post content',
    'created_at' => $db->raw('CURRENT_TIMESTAMP'),
    'user_id' => 5,
    'position' => $db->raw('(SELECT MAX(position) + 1 FROM posts)')
]);
```

### Multi-insertion Performance

When inserting multiple rows, using the multi-insertion feature is much more efficient than multiple individual inserts:

```php
// Efficient way to insert multiple rows
$db->insert('logs', [
    ['action' => 'login', 'user_id' => 1, 'ip' => '192.168.1.1'],
    ['action' => 'view', 'user_id' => 1, 'ip' => '192.168.1.1'],
    ['action' => 'edit', 'user_id' => 1, 'ip' => '192.168.1.1'],
    ['action' => 'logout', 'user_id' => 1, 'ip' => '192.168.1.1']
]);
```

### PDOStatement Usage

When you need more control over the insert operation, you can get the PDOStatement by setting the third parameter to `true`:

```php
// Get the PDOStatement
$statement = $db->insert('users', [
    'name' => 'Advanced User',
    'email' => 'advanced@example.com'
], true);

if ($statement) {
    // Get row count
    $rowCount = $statement->rowCount();
    
    // You can use other PDOStatement methods as needed
    // For example, if your database supports returning inserted values:
    // $insertedData = $statement->fetch(PDO::FETCH_ASSOC);
}
```

### Direct Access to the Insert Model

You can also access the Insert model directly for more advanced operations:

```php
// Get the Insert model instance
$insert = $db->getInsert();

// Execute an insert operation
$result = $insert->execute('users', [
    'name' => 'Direct Access User',
    'email' => 'direct@example.com'
]);

// Get the last insert ID
$lastId = $insert->lastInsertId();

// Get the last PDOStatement
$statement = $insert->getStatement();
```

## Table Prefix

If you specified a table prefix when creating the FoxQL instance, it will be automatically applied to the table name.

```php
$db = new \FoxQL\FoxQL([
    'type' => 'mysql',
    'database' => 'my_database',
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'password',
    'prefix' => 'fox_'
]);

// This will insert into the 'fox_users' table
$db->insert('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

## Error Handling

If an insert operation fails, you can get the error message using the `getError` method.

```php
$result = $db->insert('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

if ($result === null) {
    echo "Error: " . $db->getError();
    
    // Get detailed error information
    $errorInfo = $db->getErrorInfo();
    print_r($errorInfo);
}
```

## Advanced Usage

### Accessing the Insert Model Directly

You can access the Insert model directly for more advanced operations.

```php
$insertModel = $db->getInsert();

$result = $insertModel->execute('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

### Inserting Data within a Transaction

```php
$db->beginTransaction();

try {
    $db->insert('users', [
        'name' => 'Alice',
        'email' => 'alice@example.com'
    ]);
    
    $db->insert('user_profiles', [
        'user_id' => $db->lastInsertId(),
        'bio' => 'Alice is a software developer.'
    ]);
    
    $db->commit();
    echo "Transaction committed successfully!";
} catch (\Exception $e) {
    $db->rollBack();
    echo "Transaction rolled back: " . $e->getMessage();
}
```

## Security Considerations

The Insert functionality in FoxQL uses parameterized queries to prevent SQL injection attacks. All values are properly escaped before being sent to the database.

```php
// This is safe from SQL injection
$db->insert('users', [
    'name' => $_POST['name'],       // User input
    'email' => $_POST['email']      // User input
]);
```

However, table names and column names are not parameterized. Make sure to validate these values if they come from user input.

```php
// UNSAFE if $tableName comes from user input
$tableName = $_GET['table'];  // DO NOT DO THIS
$db->insert($tableName, [...]);

// UNSAFE if column names come from user input
$data = [];
foreach ($_POST as $key => $value) {
    $data[$key] = $value;  // DO NOT DO THIS
}
$db->insert('users', $data);
```
