# Create Table Functionality

The `create` method allows you to create database tables with specified columns and optional table settings.

## Method Signature

```php
public function create(string $table, array $columns, $options = null): ?PDOStatement
```

### Parameters

- **table** (string): The table name.
- **columns** (array): Columns definition.
- **options** (array|string, optional): Additional table options for creating a table.

### Return Value

- **PDOStatement|null**: The PDOStatement object on success, or null on failure.

## Usage Examples

### Basic Example

Split every column definition into an array and they will be combined:

```php
$database->create("account", [
    "id" => [
        "INT",
        "NOT NULL",
        "AUTO_INCREMENT",
        "PRIMARY KEY"
    ],
    "first_name" => [
        "VARCHAR(30)",
        "NOT NULL"
    ]
]);
```

This will generate SQL similar to:

```sql
CREATE TABLE IF NOT EXISTS account (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(30) NOT NULL
)
```

### Advanced Example

You can also set raw strings as column definitions for additional options. The `<column_name>` syntax is supported for quotation shortcut:

```php
$database->create("account", [
    "id" => [
        "INT",
        "NOT NULL",
        "AUTO_INCREMENT"
    ],
    "email" => [
        "VARCHAR(70)",
        "NOT NULL",
        "UNIQUE"
    ],
    "PRIMARY KEY (<id>)"
], [
    "ENGINE" => "MyISAM",
    "AUTO_INCREMENT" => 200
]);
```

This will generate SQL similar to:

```sql
CREATE TABLE IF NOT EXISTS account (
    id INT NOT NULL AUTO_INCREMENT,
    email VARCHAR(70) NOT NULL UNIQUE,
    PRIMARY KEY (`id`)
)
ENGINE = MyISAM,
AUTO_INCREMENT = 200
```

### Creating Tables with Foreign Keys

```php
$database->create("posts", [
    "id" => [
        "INT",
        "NOT NULL",
        "AUTO_INCREMENT",
        "PRIMARY KEY"
    ],
    "user_id" => [
        "INT",
        "NOT NULL"
    ],
    "title" => [
        "VARCHAR(200)",
        "NOT NULL"
    ],
    "content" => [
        "TEXT"
    ],
    "FOREIGN KEY (<user_id>) REFERENCES users(<id>) ON DELETE CASCADE"
], [
    "ENGINE" => "InnoDB"
]);
```

### Creating Junction Tables for Many-to-Many Relationships

```php
$database->create("post_tags", [
    "post_id" => [
        "INT",
        "NOT NULL"
    ],
    "tag_id" => [
        "INT",
        "NOT NULL"
    ],
    "PRIMARY KEY (<post_id>, <tag_id>)",
    "FOREIGN KEY (<post_id>) REFERENCES posts(<id>) ON DELETE CASCADE",
    "FOREIGN KEY (<tag_id>) REFERENCES tags(<id>) ON DELETE CASCADE"
]);
```

## Direct Access to Create Model

You can also access the Create model directly:

```php
$createModel = $database->getCreate();
$createModel->execute("comments", [
    "id" => [
        "INT",
        "NOT NULL",
        "AUTO_INCREMENT",
        "PRIMARY KEY"
    ],
    "content" => [
        "TEXT",
        "NOT NULL"
    ]
]);
```

## Error Handling

The `create` method catches PDOExceptions and stores the error message. You can check for errors after calling the method:

```php
$result = $database->create("users", [
    "id" => [
        "INT",
        "NOT NULL",
        "AUTO_INCREMENT",
        "PRIMARY KEY"
    ],
    "username" => [
        "VARCHAR(50)",
        "NOT NULL",
        "UNIQUE"
    ]
]);

if ($result === null) {
    echo "Error: " . $database->error();
} else {
    echo "Table created successfully!";
}
```