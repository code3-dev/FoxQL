# Delete

Delete data from the table.

## Basic Usage

```php
$database->delete("account", [
    "AND" => [
        "type" => "business",
        "age[<]" => 18
    ]
]);
```

## Method Parameters

### delete($table, $where)

| Parameter | Type | Description |
|-----------|------|-------------|
| table | string | The table name. |
| where | array | The WHERE clause to filter records. |

### Return Value

`PDOStatement` - The PDOStatement object.

## Examples

### Basic Delete

```php
// Delete all records from the account table
$database->delete("account");

// Delete records with specific conditions
$database->delete("account", [
    "user_id" => 10
]);
```

### Using Operators in WHERE Clause

```php
// Delete records where age is less than 18
$database->delete("account", [
    "age[<]" => 18
]);

// Delete records where status is not active
$database->delete("account", [
    "status[!=]" => "active"
]);

// Delete records where name starts with 'A'
$database->delete("account", [
    "name[LIKE]" => "A%"
]);

// Delete records where id is in a list
$database->delete("account", [
    "id[IN]" => [1, 2, 3, 4, 5]
]);
```

### Using Logical Operators

```php
// Delete records that match all conditions (AND)
$database->delete("account", [
    "AND" => [
        "type" => "business",
        "age[<]" => 18
    ]
]);

// Delete records that match any condition (OR)
$database->delete("account", [
    "OR" => [
        "type" => "personal",
        "age[>]" => 65
    ]
]);
```

### Using Raw SQL Expressions

```php
// Delete records using a raw SQL expression
$database->delete("account", [
    "last_login" => $database->raw("< NOW() - INTERVAL 1 YEAR")
]);
```

### Getting Affected Rows

```php
// Get the number of rows affected by the delete operation
$statement = $database->delete("account", [
    "type" => "business"
]);

// Returns the number of rows affected by the last SQL statement
echo $statement->rowCount();
```

### Direct Access to Delete Model

```php
// Get the Delete model instance for advanced usage
$delete = $database->getDelete();

// Execute a delete operation
$statement = $delete->execute("account", [
    "type" => "business"
]);

// Check for errors
if ($statement === null) {
    echo $delete->getError();
}
```

For more information about PDOStatement methods, see the [PHP documentation](http://php.net/manual/en/class.pdostatement.php).