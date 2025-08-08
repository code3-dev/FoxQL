# Min Documentation

The `min` method allows you to get the minimum value of a column in a database table.

## Basic Usage

### min($table, $column, $where)

Gets the minimum value of the specified column from a table.

#### Parameters

- `$table` (string): The name of the table
- `$column` (string): The target column to calculate the minimum value from
- `$where` (array|null): Optional WHERE conditions to filter records

#### Return Value

- `string|null`: The minimum value of the column, or `null` if no records found

#### Examples

```php
// Get the minimum age from users
$minAge = $db->min('users', 'age');

// Get the minimum age of male users
$minMaleAge = $db->min('users', 'age', [
    'gender' => 'male'
]);

// Get the minimum salary with complex WHERE conditions
$minSalary = $db->min('employees', 'salary', [
    'department' => 'Engineering',
    'hire_date[>]' => '2020-01-01',
    'OR' => [
        'position' => ['Junior', 'Associate'],
        'performance_rating[<]' => 3
    ]
]);

// Display the minimum value
echo "The age of the youngest male user is {$minMaleAge}.";
```

### minJoin($table, $join, $column, $where)

Gets the minimum value of the specified column from a table with support for table joins.

#### Parameters

- `$table` (string): The name of the main table
- `$join` (array): The join conditions
- `$column` (string): The target column to calculate the minimum value from
- `$where` (array|null): Optional WHERE conditions

#### Return Value

- `string|null`: The minimum value of the column, or `null` if no records found

#### Examples

```php
// Get the minimum price of products in a specific category
$minPrice = $db->minJoin('products', [
    '[>]categories' => ['category_id' => 'id']
], 'products.price', [
    'categories.name' => 'Electronics'
]);

// Get the minimum order amount for a specific customer
$minOrderAmount = $db->minJoin('orders', [
    '[>]customers' => ['customer_id' => 'id']
], 'orders.total_amount', [
    'customers.email' => 'john@example.com'
]);

// Get the minimum salary in a department with complex conditions
$minDeptSalary = $db->minJoin('employees', [
    '[>]departments' => ['department_id' => 'id']
], 'employees.salary', [
    'departments.name' => 'Engineering',
    'employees.status' => 'active',
    'employees.hire_date[>]' => '2020-01-01'
]);

// Display the minimum value
echo "The cheapest electronic product costs ${minPrice}.";
```

### getMin()

Gets the Min model instance for direct access to its methods.

#### Return Value

- `\FoxQL\Models\Min`: The Min model instance

#### Example

```php
// Get the Min model instance
$minModel = $db->getMin();

// Use the model directly
$minSalary = $minModel->execute('employees', 'salary', [
    'department' => 'Engineering'
]);
```

## Advanced Usage

### Using with Numeric Functions

You can use the `min` method with various SQL numeric functions:

```php
// Get the minimum calculated value
$minTotal = $db->minJoin('order_items', [
    '[>]orders' => ['order_id' => 'id']
], 'order_items.quantity * order_items.price', [
    'orders.status' => 'completed'
]);
```

### Complex Conditions

```php
// Get minimum value with complex conditions
$minValue = $db->min('products', 'price', [
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
// Get minimum value using raw SQL
$minDate = $db->min('orders', $db->raw('DATE(created_at)'), [
    'status' => 'completed'
]);

// Get minimum calculated value using raw SQL
$minDiscount = $db->min('products', $db->raw('price * discount_rate'));
```

## Error Handling

The `min` and `minJoin` methods return `null` if an error occurs or if no records are found. You can check the last error message using the `getError()` method:

```php
$minValue = $db->min('non_existent_table', 'column');

if ($minValue === null) {
    echo "Error or no records: " . $db->getError();
}
```