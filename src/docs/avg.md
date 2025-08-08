# Avg Documentation

The `avg` method allows you to calculate the average value of a column in a database table.

## Basic Usage

### avg($table, $column, $where)

Calculates the average value of the specified column from a table.

#### Parameters

- `$table` (string): The name of the table
- `$column` (string): The target column to calculate the average value from
- `$where` (array|null): Optional WHERE conditions to filter records

#### Return Value

- `string|null`: The average value of the column, or `null` if no records found

#### Examples

```php
// Get the average age from users
$avgAge = $db->avg('users', 'age');

// Get the average age of male users
$avgMaleAge = $db->avg('users', 'age', [
    'gender' => 'male'
]);

// Get the average salary with complex WHERE conditions
$avgSalary = $db->avg('employees', 'salary', [
    'department' => 'Engineering',
    'hire_date[>]' => '2020-01-01',
    'OR' => [
        'position' => ['Junior', 'Associate'],
        'performance_rating[<]' => 3
    ]
]);

// Display the average value
echo "The average age of male users is {$avgMaleAge}.";
```

### avgJoin($table, $join, $column, $where)

Calculates the average value of the specified column from a table with support for table joins.

#### Parameters

- `$table` (string): The name of the main table
- `$join` (array): The join conditions
- `$column` (string): The target column to calculate the average value from
- `$where` (array|null): Optional WHERE conditions

#### Return Value

- `string|null`: The average value of the column, or `null` if no records found

#### Examples

```php
// Get the average price of products in a specific category
$avgPrice = $db->avgJoin('products', [
    '[>]categories' => ['category_id' => 'id']
], 'products.price', [
    'categories.name' => 'Electronics'
]);

// Get the average order amount for a specific customer
$avgOrderAmount = $db->avgJoin('orders', [
    '[>]customers' => ['customer_id' => 'id']
], 'orders.total_amount', [
    'customers.email' => 'john@example.com'
]);

// Get the average salary in a department with complex conditions
$avgDeptSalary = $db->avgJoin('employees', [
    '[>]departments' => ['department_id' => 'id']
], 'employees.salary', [
    'departments.name' => 'Engineering',
    'employees.status' => 'active',
    'employees.hire_date[>]' => '2020-01-01'
]);

// Display the average value
echo "The average price of electronic products is ${avgPrice}.";
```

### getAvg()

Gets the Avg model instance for direct access to its methods.

#### Return Value

- `\FoxQL\Models\Avg`: The Avg model instance

#### Example

```php
// Get the Avg model instance
$avgModel = $db->getAvg();

// Use the model directly
$avgSalary = $avgModel->execute('employees', 'salary', [
    'department' => 'Engineering'
]);
```

## Advanced Usage

### Using with Numeric Functions

You can use the `avg` method with various SQL numeric functions:

```php
// Get the average calculated value
$avgTotal = $db->avgJoin('order_items', [
    '[>]orders' => ['order_id' => 'id']
], 'order_items.quantity * order_items.price', [
    'orders.status' => 'completed'
]);
```

### Complex Conditions

```php
// Get average value with complex conditions
$avgValue = $db->avg('products', 'price', [
    'category_id' => [1, 2, 3],
    'stock[>]' => 0,
    'price[>]' => 0,
    'OR' => [
        'on_sale' => 1,
        'AND' => [
            'rating[>]' => 4,
            'created_at[>]' => '2023-01-01'
        ]
    ]
]);
```

### Using Raw Expressions

```php
// Get average value using raw SQL
$avgDate = $db->avg('orders', $db->raw('EXTRACT(DAY FROM created_at)'), [
    'status' => 'completed'
]);

// Get average calculated value using raw SQL
$avgDiscount = $db->avg('products', $db->raw('price * discount_rate'));
```

## Error Handling

The `avg` and `avgJoin` methods return `null` if an error occurs or if no records are found. You can check the last error message using the `getError()` method:

```php
$avgValue = $db->avg('non_existent_table', 'column');

if ($avgValue === null) {
    echo "Error or no records: " . $db->getError();
}
```