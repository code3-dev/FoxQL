# FoxQL - Connect Documentation

## Overview

The Connect functionality in FoxQL provides a simple and secure way to establish database connections. It supports multiple database types including MySQL, PostgreSQL, SQLite, Sybase, Oracle, and MSSQL.

## Basic Usage

```php
// Create a new FoxQL instance with connection options
$db = new \FoxQL\FoxQL([
    'type' => 'mysql',           // Required: Database type
    'database' => 'my_database', // Required: Database name
    'host' => 'localhost',       // Required for most database types
    'username' => 'root',        // Required for most database types
    'password' => 'password',    // Optional
    'charset' => 'utf8mb4',      // Optional
    'prefix' => 'prefix_'        // Optional: Table prefix
]);

// Now you can use the connection
$results = $db->query('SELECT * FROM users');
```

## Connection Options

### Common Options

| Option | Description | Required |
|--------|-------------|----------|
| `type` | Database type (mysql, pgsql, sqlite, sybase, oracle, mssql) | Yes |
| `database` | Database name | Yes |
| `host` | Database host | Yes (except SQLite) |
| `username` | Database username | Yes (except SQLite) |
| `password` | Database password | No |
| `charset` | Character set | No |
| `collation` | Collation (MySQL specific) | No |
| `prefix` | Table prefix | No |
| `port` | Database port | No |

### MySQL Specific Options

```php
$db = new \FoxQL\FoxQL([
    'type' => 'mysql',
    'database' => 'my_database',
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci', // MySQL specific
    'port' => 3306,                      // Optional
    'socket' => '/path/to/socket',       // Optional, alternative to host/port
    'prefix' => 'prefix_'
]);
```

### PostgreSQL Specific Options

```php
$db = new \FoxQL\FoxQL([
    'type' => 'pgsql',
    'database' => 'my_database',
    'host' => 'localhost',
    'username' => 'postgres',
    'password' => 'password',
    'charset' => 'UTF8',
    'port' => 5432,                      // Optional
    'prefix' => 'prefix_'
]);
```

### SQLite Specific Options

```php
$db = new \FoxQL\FoxQL([
    'type' => 'sqlite',
    'database' => '/path/to/database.sqlite' // Can be :memory: for in-memory database
]);
```

### Oracle Specific Options

```php
$db = new \FoxQL\FoxQL([
    'type' => 'oracle',
    'database' => 'XE',
    'host' => 'localhost',
    'username' => 'system',
    'password' => 'password',
    'charset' => 'AL32UTF8',
    'port' => 1521,                      // Optional
    'prefix' => 'prefix_'
]);
```

### MSSQL Specific Options

```php
// Using sqlsrv driver (recommended for Windows)
$db = new \FoxQL\FoxQL([
    'type' => 'mssql',
    'database' => 'my_database',
    'host' => 'localhost',
    'username' => 'sa',
    'password' => 'password',
    'port' => 1433,                      // Optional
    'prefix' => 'prefix_',
    'encrypt' => true,                   // MSSQL specific
    'trust_server_certificate' => true   // MSSQL specific
]);

// Using dblib driver (for Linux/Unix)
$db = new \FoxQL\FoxQL([
    'type' => 'mssql',
    'driver' => 'dblib',                 // Specify dblib driver
    'database' => 'my_database',
    'host' => 'localhost',
    'username' => 'sa',
    'password' => 'password',
    'charset' => 'UTF-8',
    'port' => 1433,                      // Optional
    'prefix' => 'prefix_'
]);
```

### Sybase Specific Options

```php
$db = new \FoxQL\FoxQL([
    'type' => 'sybase',
    'database' => 'my_database',
    'host' => 'localhost',
    'username' => 'sa',
    'password' => 'password',
    'port' => 5000,                      // Optional
    'prefix' => 'prefix_'
]);
```

## Advanced Usage

### Using an Existing PDO Instance

```php
// Create a PDO instance
$pdo = new PDO('mysql:host=localhost;dbname=my_database', 'root', 'password');

// Use it with FoxQL
$db = new \FoxQL\FoxQL([
    'type' => 'mysql',
    'pdo' => $pdo
]);
```

### Error Handling

```php
try {
    $db = new \FoxQL\FoxQL([
        'type' => 'mysql',
        'database' => 'my_database',
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'wrong_password'
    ]);
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
```

### Checking Connection Status

```php
$db = new \FoxQL\FoxQL([/* connection options */]);

// Get the connection instance
$connection = $db->getConnection();

// Check if connected
if ($connection->isConnected()) {
    echo "Connected to the database!";
}

// Get error information
if ($error = $db->getError()) {
    echo "Error: " . $error;
    
    // Get detailed error info
    print_r($db->getErrorInfo());
}
```

## Transactions

```php
$db = new \FoxQL\FoxQL([/* connection options */]);

try {
    // Start transaction
    $db->beginTransaction();
    
    // Execute queries
    $db->execute('INSERT INTO users (name) VALUES (?)', ['John']);
    $db->execute('UPDATE users SET status = ? WHERE name = ?', ['active', 'John']);
    
    // Commit transaction
    $db->commit();
} catch (\Exception $e) {
    // Roll back transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    echo "Transaction failed: " . $e->getMessage();
}
```