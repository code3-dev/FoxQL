# Max Documentation

The `max` method allows you to get the maximum value of a column in a database table.

## Basic Usage

### max($table, $column, $where)

Gets the maximum value of the specified column from a table.

#### Parameters

- `$table` (string): The name of the table
- `$column` (string): The target column to calculate the maximum value from
- `$where` (array|null): Optional WHERE conditions to filter records

#### Return Value

- `string|null`: The maximum value of the column, or `null` if no records found

#### Examples

```php
// Get the maximum age from users
$maxAge = $db->max('users', 'age');

// Get the maximum age of active users
$maxActiveAge = $db->max('users', 'age', [
    'status' => 'active'
]);

// Get the maximum salary with complex WHERE conditions
$maxSalary = $db->max('employees', 'salary', [
    'department' => 'Engineering',
    'hire_date[>]' => '2020-01-01',
    'OR' => [
        'position' => ['Senior', 'Lead'],
        'performance_rating[>]' => 4
    ]
]);

// Display the maximum value
echo "The oldest active user is {$maxActiveAge} years old.";
```

### maxJoin($table, $join, $column, $where)

Gets the maximum value of the specified column from a table with support for table joins.

#### Parameters

- `$table` (string): The name of the main table
- `$join` (array): The join conditions
- `$column` (string): The target column to calculate the maximum value from
- `$where` (array|null): Optional WHERE conditions

#### Return Value

- `string|null`: The maximum value of the column, or `null` if no records found

#### Examples

```php
// Get the maximum price of products in a specific category
$maxPrice = $db->maxJoin('products', [
    '[>]categories' => ['category_id' => 'id']
], 'products.price', [
    'categories.name' => 'Electronics'
]);

// Get the maximum order amount for a specific customer
$maxOrderAmount = $db->maxJoin('orders', [
    '[>]customers' => ['customer_id' => 'id']
], 'orders.total_amount', [
    'customers.email' => 'john@example.com'
]);

// Get the maximum salary in a department with complex conditions
$maxDeptSalary = $db->maxJoin('employees', [
    '[>]departments' => ['department_id' => 'id']
], 'employees.salary', [
    'departments.name' => 'Engineering',
    'employees.status' => 'active',
    'employees.hire_date[>]' => '2020-01-01'
]);

// Display the maximum value
echo "The most expensive electronic product costs ${maxPrice}.";
```

### getMax()

Gets the Max model instance for direct access to its methods.

#### Return Value

- `\FoxQL\Models\Max`: The Max model instance

#### Example

```php
// Get the Max model instance
$maxModel = $db->getMax();

// Use the model directly
$maxSalary = $maxModel->execute('employees', 'salary', [
    'department' => 'Engineering'
]);
```

## Advanced Usage

### Using with Numeric Functions

You can use the `max` method with various SQL numeric functions:

```php
// Get the maximum calculated value
$maxTotal = $db->maxJoin('order_items', [
    '[>]orders' => ['order_id' => 'id']
], 'order_items.quantity * order_items.price', [
    'orders.status' => 'completed'
]);
```

### Complex Conditions

```php
// Get maximum value with complex conditions
$maxValue = $db->max('products', 'price', [
    'category_id' => [1, 2, 3],
    'stock[>]' => 0,
    'price[<]' => 1000,
    'OR' => [
        'featured' => 1,
        'AND' => [
            'rating[>]' => 4,
            'created_at[>]' => '2023-01-01'
        ]
    ]
]);
```

### Using Raw Expressions

```php
// Get maximum value using raw SQL
$maxDate = $db->max('orders', $db->raw('DATE(created_at)'), [
    'status' => 'completed'
]);

// Get maximum calculated value using raw SQL
$maxDiscount = $db->max('products', $db->raw('price * discount_rate'));
```

## Error Handling

The `max` and `maxJoin` methods return `null` if an error occurs or if no records are found. You can check the last error message using the `getError()` method:

```php
$maxValue = $db->max('non_existent_table', 'column');

if ($maxValue === null) {
    echo "Error or no records: " . $db->getError();
}
```