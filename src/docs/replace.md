# Replace

The Replace model provides functionality for replacing specific values in database columns with new values.

## Basic Usage

```php
$db->replace(string $table, array $columns, array $where = null);
```

## Parameters

### `replace($table, $columns, $where)`

- `$table` (string) - The table name.
- `$columns` (array) - The target columns with old values to be replaced with new values.
- `$where` (array, optional) - The WHERE clause to filter records.

## Return Value

- `PDOStatement` - The PDOStatement object.

## Examples

### Basic Replacement

```php
$db->replace("account", [
    "type" => [
        "user" => "new_user",
        "business" => "new_business"
    ],
    "column" => [
        "old_value" => "new_value"
    ]
]);
```

This will replace all occurrences of "user" with "new_user" and "business" with "new_business" in the "type" column, and all occurrences of "old_value" with "new_value" in the "column" column.

### Replacement with WHERE Clause

```php
$db->replace("account", [
    "type" => [
        "user" => "new_user",
        "business" => "new_business"
    ]
], [
    "user_id[>]" => 1000
]);
```

This will replace values only in rows where user_id is greater than 1000.

### Using Operators in WHERE Clause

```php
$db->replace("account", [
    "status" => [
        "active" => "premium"
    ]
], [
    "created_at[<]" => "2023-01-01",
    "type" => "user"
]);
```

This will replace "active" with "premium" in the "status" column for all user accounts created before 2023-01-01.

### Using Logical Operators

```php
$db->replace("products", [
    "category" => [
        "electronics" => "tech"
    ]
], [
    "AND" => [
        "price[>]" => 500,
        "stock[>]" => 10
    ]
]);
```

This will replace "electronics" with "tech" in the "category" column for products that have a price greater than 500 AND stock greater than 10.

### Using Raw SQL Expressions

```php
$db->replace("users", [
    "status" => [
        "pending" => $db->raw("CONCAT('verified_', user_id)")
    ]
]);
```

This will replace "pending" in the "status" column with a dynamically generated value using SQL's CONCAT function.

### Getting Affected Rows

```php
$statement = $db->replace("account", [
    "type" => [
        "user" => "new_user"
    ]
]);

$affectedRows = $statement->rowCount();
```

### Direct Access to Replace Model

```php
$replace = $db->getReplace();

$replace->execute("account", [
    "type" => [
        "user" => "new_user"
    ]
]);
```