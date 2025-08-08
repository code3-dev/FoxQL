# Drop Table Functionality

The `drop` method allows you to drop database tables. It's convenient when the table prefix is set.

## Method Signature

```php
public function drop(string $table): ?PDOStatement
```

### Parameters

- **table** (string): The table name.

### Return Value

- **PDOStatement|null**: The PDOStatement object on success, or null on failure.

## Usage Examples

### Basic Example

```php
$database->drop("account");
```

This will generate SQL similar to:

```sql
DROP TABLE IF EXISTS `account`
```

### With Table Prefix

When a table prefix is set in the database configuration, the prefix is automatically applied:

```php
$database = new FoxQL([
    // ...
    "prefix" => "wp_"
]);

$database->drop("account");
```

This will generate SQL similar to:

```sql
DROP TABLE IF EXISTS `wp_account`
```

## Direct Access to Drop Model

You can also access the Drop model directly:

```php
$dropModel = $database->getDrop();
$dropModel->execute("account");
```

## Error Handling

The `drop` method catches PDOExceptions and stores the error message. You can check for errors after calling the method:

```php
$result = $database->drop("users");

if ($result === null) {
    echo "Error: " . $database->getError();
} else {
    echo "Table dropped successfully!";
}
```

## Important Notes

1. This API is only for dropping a table. For dropping databases, schema, event, index, or other database resources, use `exec()` to execute raw commands instead.

2. The method uses `DROP TABLE IF EXISTS` to prevent errors when the table doesn't exist.

3. Table identifiers are properly quoted according to the database type (e.g., backticks for MySQL, double quotes for PostgreSQL).