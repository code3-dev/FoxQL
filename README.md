<div align="center">

# 🦊 FoxQL

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.2-8892BF.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Composer Package](https://img.shields.io/badge/Composer-foxql%2Ffoxql-blue)](https://packagist.org/packages/foxql/foxql)
[![GitHub](https://img.shields.io/badge/GitHub-code3--dev%2FFoxQL-black.svg)](https://github.com/code3-dev/FoxQL)

**A fast, secure, and easy modern SQL library for PHP**

*Seamlessly work with PostgreSQL, MySQL, SQLite, Sybase, Oracle, and MSSQL databases*

</div>

---

## ✨ Features

- 🚀 **Simple & Intuitive API** - Write less, do more with a clean, fluent interface
- 🔒 **Secure by Default** - Built-in protection against SQL injection
- 🔄 **Multi-Database Support** - Works with PostgreSQL, MySQL, SQLite, Sybase, Oracle, and MSSQL
- 🧩 **Modular Design** - Use only what you need with a lightweight core
- 📊 **Powerful Query Builder** - Construct complex queries with ease
- 🛠️ **Schema Management** - Create and modify database structures programmatically
- 🏗️ **Migration System** - Laravel-inspired migrations for managing database schema changes

## 📦 Installation

### Using Composer (Recommended)

```bash
composer require foxql/foxql
```

## 🚀 Quick Start

```php
// Create a new FoxQL instance with MySQL connection
try {
    $db = new \FoxQL\FoxQL([
        'type' => 'mysql',        // Database type: mysql, pgsql, sqlite, etc.
        'database' => 'my_database',
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'password',
        'charset' => 'utf8mb4',   // Character set
        'prefix' => 'prefix_'      // Table prefix (optional)
    ]);
    
    // Insert data
    $db->insert('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
    
    // Select data
    $users = $db->select('users', '*');
    print_r($users);
    
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
```

## 🏗️ Database Migrations

FoxQL includes a powerful migration system for managing database schema changes:

```php
// Create a table using the migration builder
$db->createTable('users', function($table) {
    $table->increments('id');
    $table->string('name', 100)->notNull();
    $table->string('email', 100)->notNull()->unique();
    $table->timestamps(); // Adds created_at and updated_at columns
});

// Run migrations from a directory
$migrations = $db->migrate('/path/to/migrations');

// See the complete example in examples/migration_example.php
```
## 📚 Documentation

Explore our comprehensive documentation for detailed usage instructions:

### 🔄 Core Operations

| Basic Operations | Data Manipulation | Schema Management | Raw SQL |
| ---------------- | ----------------- | ----------------- | ------- |
| [Connection](src/docs/connect.md) | [Insert](src/docs/insert.md) | [Create Table](src/docs/create.md) | [Query](src/docs/query.md) |
| [Select](src/docs/select.md) | [Update](src/docs/update.md) | [Drop Table](src/docs/drop.md) | [Execute](src/docs/execute.md) |
| [Get](src/docs/get.md) | [Delete](src/docs/delete.md) | [Action](src/docs/action.md) | |
| [Has](src/docs/has.md) | [Replace](src/docs/replace.md) | [Migration](src/docs/migration.md) | |

### 📊 Aggregation Functions

| Statistical | Mathematical | Other |
| ----------- | ------------ | ----- |
| [Count](src/docs/count.md) | [Sum](src/docs/sum.md) | [Rand](src/docs/rand.md) |
| [Average](src/docs/avg.md) | [Max](src/docs/max.md) | |
| | [Min](src/docs/min.md) | |

## 📄 License

FoxQL is open-sourced software licensed under the [MIT license](LICENSE).

## 👨‍💻 Developer

- **Name:** Hossein Pira
- **Telegram:** [@h3dev](https://t.me/h3dev)
- **Email:** h3dev.pira@gmail.com
- **LinkedIn:** [https://www.linkedin.com/in/hossein-pira-748056278](https://www.linkedin.com/in/hossein-pira-748056278)