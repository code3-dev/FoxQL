# Execute Method Documentation

The `execute` method allows you to run raw SQL queries and get the number of affected rows. This is useful for operations where you need to execute custom SQL that isn't covered by the library's built-in methods.

## Method Signature

```php
public function execute(string $query, array $params = []): ?int
```

### Parameters

- **query** (string): The SQL query to execute.
- **params** (array, optional): An array of parameters to bind to the query.

### Return Value

- **int|null**: The number of affected rows, or null on failure.

## Usage Examples

### Basic Example

```php
// Execute a simple query
$affectedRows = $database->execute("UPDATE users SET status = 'active' WHERE id = ?", [1]);

if ($affectedRows !== null) {
    echo "Updated $affectedRows row(s)";
} else {
    echo "Error: " . $database->getError();
}
```

### Multiple Parameters

```php
// Execute a query with multiple parameters
$affectedRows = $database->execute(
    "UPDATE products SET price = ?, stock = ? WHERE category = ?", 
    [19.99, 100, 'electronics']
);

if ($affectedRows !== null) {
    echo "Updated $affectedRows row(s)";
} else {
    echo "Error: " . $database->getError();
}
```

### Named Parameters

```php
// Execute a query with named parameters
$affectedRows = $database->execute(
    "UPDATE users SET email = :email, updated_at = :time WHERE id = :id", 
    [
        ':email' => 'new.email@example.com',
        ':time' => date('Y-m-d H:i:s'),
        ':id' => 5
    ]
);

if ($affectedRows !== null) {
    echo "Updated $affectedRows row(s)";
} else {
    echo "Error: " . $database->getError();
}
```

### Database-Specific Operations

```php
// MySQL: Enable foreign key checks
$database->execute("SET FOREIGN_KEY_CHECKS = 1");

// PostgreSQL: Set search path
$database->execute("SET search_path TO my_schema");

// SQLite: Enable foreign keys
$database->execute("PRAGMA foreign_keys = ON");
```

## Error Handling

The `execute` method catches PDOExceptions and stores the error message. You can check for errors after calling the method:

```php
$result = $database->execute("INVALID SQL QUERY");

if ($result === null) {
    echo "Error: " . $database->getError();
    
    // Get detailed error info
    print_r($database->getErrorInfo());
}
```

## Security Considerations

Always use parameterized queries with the `execute` method to prevent SQL injection attacks:

```php
// GOOD: Using parameters
$database->execute("DELETE FROM users WHERE id = ?", [$userId]);

// BAD: Directly inserting variables into the query
$database->execute("DELETE FROM users WHERE id = " . $userId); // VULNERABLE!
```

## Database-Specific Syntax

FoxQL supports multiple database types, each with its own SQL syntax. When using the `execute` method, make sure to use the correct SQL syntax for your database type.

### MySQL Example

```php
$database->execute("ALTER TABLE users ADD COLUMN last_login TIMESTAMP");
```

### PostgreSQL Example

```php
$database->execute("ALTER TABLE users ADD COLUMN last_login TIMESTAMP");
```

### SQLite Example

```php
$database->execute("ALTER TABLE users ADD COLUMN last_login TEXT");
```

## Important Notes

1. The `execute` method is designed for queries that modify data (INSERT, UPDATE, DELETE) or execute commands, and returns the number of affected rows.

2. For queries that return data, use the `query`, `queryOne`, or `queryValue` methods instead.

3. The method uses prepared statements internally, which provides protection against SQL injection when used correctly with parameters.

4. For complex operations or transactions, consider using multiple `execute` calls within a transaction block.