# Query Method Documentation

The `query` method allows you to execute raw SQL queries and retrieve the result set as an array. This is useful when you need to run custom SQL queries that aren't covered by the library's built-in methods.

## Method Signature

```php
public function query(string $query, array $params = []): ?array
```

### Parameters

- **query** (string): The SQL query to execute.
- **params** (array, optional): An array of parameters to bind to the query.

### Return Value

- **array|null**: An array containing all of the result set rows, or null on failure.

## Related Methods

FoxQL provides additional query methods for specific use cases:

- **queryOne**: Executes a query and returns a single row.
- **queryValue**: Executes a query and returns a single value.
- **execute**: Executes a query and returns the number of affected rows (for non-SELECT queries).

## Usage Examples

### Basic Example

```php
// Execute a simple SELECT query
$results = $database->query("SELECT * FROM users WHERE status = ?", ['active']);

if ($results !== null) {
    foreach ($results as $user) {
        echo "User: {$user['name']}\n";
    }
} else {
    echo "Error: " . $database->getError();
}
```

### Complex Query

```php
// Execute a more complex query with multiple parameters
$results = $database->query(
    "SELECT p.*, c.name as category_name 
     FROM products p 
     JOIN categories c ON p.category_id = c.id 
     WHERE p.price > ? AND c.active = ?", 
    [19.99, 1]
);

if ($results !== null) {
    foreach ($results as $product) {
        echo "Product: {$product['name']} (Category: {$product['category_name']})\n";
    }
} else {
    echo "Error: " . $database->getError();
}
```

### Named Parameters

```php
// Execute a query with named parameters
$results = $database->query(
    "SELECT * FROM users WHERE created_at > :date AND status = :status", 
    [
        ':date' => '2023-01-01',
        ':status' => 'active'
    ]
);

if ($results !== null) {
    echo "Found " . count($results) . " users";
} else {
    echo "Error: " . $database->getError();
}
```

### Using queryOne

```php
// Get a single row
$user = $database->queryOne("SELECT * FROM users WHERE id = ?", [5]);

if ($user !== null) {
    echo "User: {$user['name']} ({$user['email']})";
} else {
    echo "User not found or error occurred";
}
```

### Using queryValue

```php
// Get a single value
$count = $database->queryValue("SELECT COUNT(*) FROM products WHERE category_id = ?", [3]);

if ($count !== null) {
    echo "Number of products: $count";
} else {
    echo "Error: " . $database->getError();
}
```

## Error Handling

The `query` method catches PDOExceptions and stores the error message. You can check for errors after calling the method:

```php
$results = $database->query("INVALID SQL QUERY");

if ($results === null) {
    echo "Error: " . $database->getError();
    
    // Get detailed error info
    print_r($database->getErrorInfo());
}
```

## Security Considerations

Always use parameterized queries with the `query` method to prevent SQL injection attacks:

```php
// GOOD: Using parameters
$results = $database->query("SELECT * FROM users WHERE username = ?", [$username]);

// BAD: Directly inserting variables into the query
$results = $database->query("SELECT * FROM users WHERE username = '" . $username . "'"); // VULNERABLE!
```

## Database-Specific Syntax

FoxQL supports multiple database types, each with its own SQL syntax. When using the `query` method, make sure to use the correct SQL syntax for your database type.

### MySQL Example

```php
$results = $database->query("SELECT * FROM users LIMIT 10");
```

### PostgreSQL Example

```php
$results = $database->query("SELECT * FROM users LIMIT 10");
```

### SQLite Example

```php
$results = $database->query("SELECT * FROM users LIMIT 10");
```

## Important Notes

1. The `query` method is designed for SELECT queries that return multiple rows of data.

2. For queries that modify data (INSERT, UPDATE, DELETE), use the `execute` method instead.

3. For retrieving a single row or value, consider using the more specific `queryOne` or `queryValue` methods.

4. The method uses prepared statements internally, which provides protection against SQL injection when used correctly with parameters.

5. The result set is returned as an associative array, where each element represents a row from the result set.