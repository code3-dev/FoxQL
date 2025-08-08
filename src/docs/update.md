# Update

Modify data in database tables.

## Basic Usage

```php
// Update a single record
$database->update("account", [
    "name" => "John Doe",
    "email" => "john.doe@example.com"
], [
    "id" => 1
]);

// Update multiple records with a WHERE condition
$database->update("account", [
    "status" => "inactive"
], [
    "last_login[<]" => date('Y-m-d', strtotime('-30 days'))
]);
```

## Methods

### update

```php
update($table, $data, $where)
```

#### Parameters

- `$table` [string]
  - The table name.

- `$data` [array]
  - The data to be updated.

- `$where` (optional) [array]
  - The WHERE clause to filter records.

#### Return Value

- [PDOStatement] The PDOStatement object.

## Examples

### Basic Update

```php
$database->update("account", [
    "name" => "John Doe",
    "email" => "john.doe@example.com"
], [
    "id" => 1
]);
```

### Mathematical Operations

You can use `[+]`, `[-]`, `[*]`, and `[/]` for mathematical operations.

```php
$database->update("account", [
    // All age plus one
    "age[+]" => 1,
    
    // All levels subtract 5
    "level[-]" => 5,
    
    // All scores multiplied by 2
    "score[*]" => 2,
    
    // All prices divided by 1.1
    "price[/]" => 1.1
], [
    "user_id[<]" => 1000
]);
```

### Array Serialization

Arrays are automatically serialized to JSON.

```php
$database->update("account", [
    // Array value
    "lang" => ["en", "fr", "jp", "cn"],
    
    // Array value encoded as JSON
    "lang [JSON]" => ["en", "fr", "jp", "cn"]
], [
    "id" => 1
]);
```

### Type Auto-Detection

FoxQL automatically detects and handles different data types.

```php
// Object data
class Foo {
    var $bar = "cat";
    
    public function __wakeup() {
        $this->bar = "dog";
    }
}

$object_data = new Foo();

// File pointer
$fp = fopen($_FILES["file"]["tmp_name"], "rb");

$database->update("account", [
    // Boolean value
    "is_locked" => true,
    
    // Object value
    "object_data" => $object_data,
    
    // Large Objects (LOBs)
    "image" => $fp
], [
    "id" => 1
]);
```

### WHERE Clause Operators

You can use various operators in the WHERE clause.

```php
$database->update("account", [
    "status" => "inactive"
], [
    "id[>]" => 100,                 // id > 100
    "age[<]" => 30,                 // age < 30
    "name[~]" => "%doe%",           // name LIKE '%doe%'
    "type[!]" => "admin",           // type != 'admin'
    "created_at[<>]" => ["2020-01-01", "2020-12-31"] // created_at BETWEEN '2020-01-01' AND '2020-12-31'
]);
```

### Raw SQL Expressions

You can use raw SQL expressions with the `raw()` method.

```php
$database->update("account", [
    "login_count" => $database->raw("login_count + 1"),
    "last_login" => $database->raw("NOW()")
], [
    "id" => 1
]);
```

### Getting Affected Rows

The returned object of `update()` is a PDOStatement, so you can use its methods to get more information.

```php
$statement = $database->update("account", [
    "status" => "inactive"
], [
    "last_login[<]" => date('Y-m-d', strtotime('-30 days'))
]);

// Returns the number of rows affected by the last SQL statement
echo $statement->rowCount();
```

### Direct Access to Update Model

You can also access the Update model directly for more control.

```php
$update = $database->getUpdate();

$statement = $update->execute("account", [
    "name" => "John Doe"
], [
    "id" => 1
]);

// Check for errors
if ($statement === null) {
    echo $update->getError();
}
```