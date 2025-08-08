# Sum

The `sum` function calculates the total value of a column in a database table.

## Basic Usage

### sum

```php
sum($table, $column, $where)
```

#### Parameters

- `$table` (string): The table name.
- `$column` (string): The target column to be calculated.
- `$where` (array, optional): The WHERE clause to filter records.

#### Return Value

- (string): The total value of the column.

#### Example

```php
// Calculate the total money in all accounts
$total = $database->sum("account", "money");
echo "We have $" . $total;

// Calculate the total money for accounts with balance > 1000
$total = $database->sum("account", "money", [
    "money[>]" => 1000
]);
```

### sumJoin

```php
sumJoin($table, $join, $column, $where)
```

#### Parameters

- `$table` (string): The table name.
- `$join` (array): Table relativity for tables.
- `$column` (string): The target column to be calculated.
- `$where` (array, optional): The WHERE clause to filter records.

#### Return Value

- (string): The total value of the column.

#### Example

```php
// Calculate the total salary for employees in the IT department
$total = $database->sumJoin("employees", [
    "[>]departments" => ["department_id" => "id"]
], "salary", [
    "departments.name" => "IT"
]);
```

## Direct Access

You can also access the Sum model directly:

```php
$sumModel = $database->getSum();
$total = $sumModel->execute("products", "price * quantity", [
    "category" => "electronics"
]);
```

## Advanced Usage

### Numeric Functions

You can use SQL numeric functions in the column parameter:

```php
// Calculate the total of price multiplied by quantity
$total = $database->sum("products", "price * quantity");

// Calculate the total of rounded prices
$total = $database->sum("products", "ROUND(price, 2)");
```

### Complex Conditions

You can use complex conditions in the where parameter:

```php
// Calculate the total money for active accounts created in the last 30 days
$total = $database->sum("account", "money", [
    "AND" => [
        "status" => "active",
        "created_at[>]" => date("Y-m-d", strtotime("-30 days"))
    ]
]);
```

### Raw Expressions

You can use raw SQL expressions with the Raw class:

```php
use FoxQL\Core\Raw;

// Calculate the total using a raw SQL expression
$total = $database->sum("products", new Raw("price * IF(discount > 0, (1 - discount), 1)"));
```

## Error Handling

The `sum` and `sumJoin` methods return `null` if an error occurs. You can check for errors using the `error()` method:

```php
$total = $database->sum("account", "money");
if ($total === null) {
    echo "Error: " . $database->error();
}
```