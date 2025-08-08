# Action (Transactions) and ID

## Action

The `action` method provides transaction support for database operations. It allows you to execute multiple queries as a single atomic operation that either all succeed or all fail.

## Basic Usage

```php
action($callback)
```

### Parameters

- `$callback` (callable): A function that contains the database operations to be executed within the transaction. The callback receives the database instance as its parameter.

### Return Value

- (bool): Returns `true` if the transaction was committed successfully, or `false` if it was rolled back.

### Exceptions

- Throws `PDOException` if the database doesn't support transactions or if an error occurs during the transaction.

## Examples

### Basic Transaction

```php
$database->action(function($database) {
    $database->insert("account", [
        "name" => "foo",
        "email" => "bar@abc.com"
    ]);
    
    $database->delete("account", [
        "user_id" => 2312
    ]);
    
    // If you found something wrong, just return a false value to roll back the whole transaction.
    if ($database->has("post", ["user_id" => 2312])) {
        return false;
    }
});
```

### Accessing Data Outside of Action

Create a result variable and refer to the action callback with the keyword `use`, and you can get data back after when you assign it from inside.

```php
$result = "";

$database->action(function($database) use (&$result) {
    $database->insert("account", [
        "user_name" => "foo"
    ]);
    
    $newId = $database->id();
    
    $result = "Account is created, and the id is {$newId}.";
});

echo $result;
```

## Transaction Behavior

- If the callback returns `false`, the transaction will be rolled back.
- If the callback returns any value other than `false`, the transaction will be committed.
- If an exception is thrown within the callback, the transaction will be rolled back and the exception will be re-thrown.

## Error Handling

```php
try {
    $database->action(function($database) {
        // Database operations
        if (/* some condition */) {
            return false; // Roll back the transaction
        }
        return true; // Commit the transaction
    });
} catch (PDOException $e) {
    echo "Transaction error: " . $e->getMessage();
}
```

## ID

The `id` method returns the ID of the last inserted row.

```php
id($name)
```

### Parameters

- `$name` (string, optional): The name of the sequence object. This is required for PostgreSQL, but not for MySQL or SQLite.

### Return Value

- (string|null): The last inserted ID, or null if an error occurs.

### Example

```php
// Insert a new record
$database->insert("users", [
    "username" => "john_doe",
    "email" => "john@example.com"
]);

// Get the ID of the inserted record
$userId = $database->id();
echo "New user ID: " . $userId;
```

## Notes

- Not all database engines support transactions. Check your database documentation before using this feature.
- Nested transactions are not supported. If you call `action()` within another `action()` callback, the behavior is undefined.
- For maximum compatibility, avoid using DDL statements (CREATE, ALTER, DROP) within transactions as some databases do not support them in transactions.
- The `id()` method relies on PDO's `lastInsertId()` function, which may behave differently across different database systems.