<?php

declare(strict_types=1);

namespace FoxQL;

use FoxQL\Models\Connect;
use FoxQL\Models\Insert;
use FoxQL\Models\Select;
use FoxQL\Models\Update;
use FoxQL\Models\Delete;
use FoxQL\Models\Replace;
use FoxQL\Models\Get;
use FoxQL\Models\Has;
use FoxQL\Models\Rand;
use FoxQL\Models\Count;
use FoxQL\Models\Max;
use FoxQL\Models\Min;
use FoxQL\Models\Avg;
use FoxQL\Models\Sum;
use FoxQL\Models\Create;
use FoxQL\Models\Drop;
use FoxQL\Core\Pdo;
use FoxQL\Core\Raw;
use PDO as NativePDO;
use PDOException;
use PDOStatement;
use InvalidArgumentException;

/**
 * FoxQL - Modern SQL Library for PHP
 * 
 * A fast, secure, and easy-to-use SQL library for PHP
 * supporting multiple database types: MySQL, PostgreSQL, SQLite, Sybase, Oracle, and MSSQL.
 *
 * @author Hossein Pira <h3dev.pira@gmail.com>
 * @link https://www.linkedin.com/in/hossein-pira-748056278
 * @telegram @h3dev
 */
class FoxQL
{
    /**
     * The database connection instance.
     *
     * @var \FoxQL\Models\Connect
     */
    protected $connection;

    /**
     * The PDO wrapper instance.
     *
     * @var \FoxQL\Core\Pdo
     */
    protected $pdo;

    /**
     * The database type.
     *
     * @var string
     */
    protected $type;

    /**
     * The table prefix.
     *
     * @var string
     */
    protected $prefix;

    /**
     * The Insert model instance.
     *
     * @var \FoxQL\Models\Insert
     */
    protected $insert;
    
    /**
     * The Select model instance.
     *
     * @var \FoxQL\Models\Select
     */
    protected $select;
    
    /**
     * The Update model instance.
     *
     * @var \FoxQL\Models\Update
     */
    protected $update;
    
    /**
     * The Delete model instance.
     *
     * @var \FoxQL\Models\Delete
     */
    protected $delete;
    
    /**
     * The Replace model instance.
     *
     * @var \FoxQL\Models\Replace
     */
    protected $replace;
    
    /**
     * The Get model instance.
     *
     * @var \FoxQL\Models\Get
     */
    protected $get;
    
    /**
     * The Has model instance.
     *
     * @var \FoxQL\Models\Has
     */
    protected $has;
    
    /**
     * The Rand model instance.
     *
     * @var \FoxQL\Models\Rand
     */
    protected $rand;
    
    /**
     * The Count model instance.
     *
     * @var \FoxQL\Models\Count
     */
    protected $count;
    
    /**
     * The Max model instance.
     *
     * @var \FoxQL\Models\Max
     */
    protected $max;
    
    /**
     * The Min model instance.
     *
     * @var \FoxQL\Models\Min
     */
    protected $min;
    
    /**
     * The Avg model instance.
     *
     * @var \FoxQL\Models\Avg
     */
    protected $avg;
    
    /**
     * The Sum model instance.
     *
     * @var \FoxQL\Models\Sum
     */
    protected $sum;
    
    /**
     * The Create model instance.
     *
     * @var \FoxQL\Models\Create
     */
    protected $create;
    
    /**
     * The Drop model instance.
     *
     * @var \FoxQL\Models\Drop
     */
    protected $drop;

    /**
     * The last error message.
     *
     * @var string|null
     */
    protected $error = null;

    /**
     * Create a new FoxQL instance.
     *
     * Example usage:
     * 
     * ```php
     * $db = new FoxQL([
     *     'type' => 'mysql',
     *     'database' => 'my_database',
     *     'host' => 'localhost',
     *     'username' => 'root',
     *     'password' => 'password',
     *     'charset' => 'utf8mb4',
     *     'prefix' => 'prefix_'
     * ]);
     * ```
     *
     * @param array $options Connection options
     * @throws PDOException If the connection fails
     */
    public function __construct(array $options)
    {
        try {
            // Create connection
            $this->connection = Connect::create($options);
            $this->pdo = $this->connection->getPdo();
            
            // Set properties
            $this->type = $this->pdo->getType();
            $this->prefix = $this->pdo->getPrefix();
            
            // Initialize models
            $this->initializeModels();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            throw $e;
        }
    }
    
    /**
     * Initialize all models.
     *
     * @return void
     */
    protected function initializeModels(): void
    {
        $this->insert = new Insert($this->pdo, $this->prefix);
        $this->select = new Select($this->pdo, $this->prefix);
        $this->update = new Update($this->pdo, $this->prefix);
        $this->delete = new Delete($this->pdo, $this->prefix);
        $this->replace = new Replace($this->pdo, $this->prefix);
        $this->get = new Get($this->pdo, $this->prefix);
        $this->has = new Has($this->pdo, $this->prefix);
        $this->rand = new Rand($this->pdo, $this->prefix);
        $this->count = new Count($this->pdo, $this->prefix);
        $this->max = new Max($this->pdo, $this->prefix);
        $this->min = new Min($this->pdo, $this->prefix);
        $this->avg = new Avg($this->pdo, $this->prefix);
        $this->sum = new Sum($this->pdo, $this->prefix);
        $this->create = new Create($this->pdo, $this->prefix);
        $this->drop = new Drop($this->pdo, $this->prefix);
    }

    /**
     * Get the database connection instance.
     *
     * @return \FoxQL\Models\Connect The database connection instance
     */
    public function getConnection(): Connect
    {
        return $this->connection;
    }

    /**
     * Get the PDO wrapper instance.
     *
     * @return \FoxQL\Core\Pdo The PDO wrapper instance
     */
    public function getPdo(): Pdo
    {
        return $this->pdo;
    }

    /**
     * Get the native PDO instance.
     *
     * @return \PDO The native PDO instance
     */
    public function getNativePdo(): NativePDO
    {
        return $this->pdo->getPdo();
    }

    /**
     * Get the database type.
     *
     * @return string The database type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the table prefix.
     *
     * @return string The table prefix
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Get the last error message.
     *
     * @return string|null The last error message
     */
    public function getError(): ?string
    {
        return $this->error ?? $this->connection->getError();
    }

    /**
     * Get the last error details.
     *
     * @return array|null The last error details
     */
    public function getErrorInfo(): ?array
    {
        return $this->connection->getErrorInfo();
    }

    /**
     * Execute a raw SQL query.
     *
     * @param string $query The SQL query
     * @param array $params The parameters to bind
     * @return array|null The result set
     */
    public function query(string $query, array $params = []): ?array
    {
        $statement = $this->pdo->execute($query, $params);

        if (!$statement) {
            $this->error = $this->pdo->getError();
            return null;
        }

        return $statement->fetchAll(NativePDO::FETCH_ASSOC);
    }

    /**
     * Execute a raw SQL query and return a single row.
     *
     * @param string $query The SQL query
     * @param array $params The parameters to bind
     * @return array|null The result row
     */
    public function queryOne(string $query, array $params = []): ?array
    {
        $statement = $this->pdo->execute($query, $params);

        if (!$statement) {
            $this->error = $this->pdo->getError();
            return null;
        }

        $result = $statement->fetch(NativePDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Execute a raw SQL query and return a single value.
     *
     * @param string $query The SQL query
     * @param array $params The parameters to bind
     * @return mixed|null The result value
     */
    public function queryValue(string $query, array $params = [])
    {
        $statement = $this->pdo->execute($query, $params);

        if (!$statement) {
            $this->error = $this->pdo->getError();
            return null;
        }

        $result = $statement->fetchColumn();
        return $result !== false ? $result : null;
    }

    /**
     * Execute a raw SQL query and return the number of affected rows.
     *
     * @param string $query The SQL query
     * @param array $params The parameters to bind
     * @return int|null The number of affected rows
     */
    public function execute(string $query, array $params = []): ?int
    {
        $statement = $this->pdo->execute($query, $params);

        if (!$statement) {
            $this->error = $this->pdo->getError();
            return null;
        }

        return $statement->rowCount();
    }

    /**
     * Begin a transaction.
     *
     * @return bool Whether the transaction was started
     */
    public function beginTransaction(): bool
    {
        return $this->getNativePdo()->beginTransaction();
    }

    /**
     * Commit a transaction.
     *
     * @return bool Whether the transaction was committed
     */
    public function commit(): bool
    {
        return $this->getNativePdo()->commit();
    }

    /**
     * Roll back a transaction.
     *
     * @return bool Whether the transaction was rolled back
     */
    public function rollBack(): bool
    {
        return $this->getNativePdo()->rollBack();
    }

    /**
     * Check if a transaction is active.
     *
     * @return bool Whether a transaction is active
     */
    public function inTransaction(): bool
    {
        return $this->getNativePdo()->inTransaction();
    }

    /**
     * Get the last inserted ID.
     *
     * @param string|null $name The name of the sequence object (if any)
     * @return string The last inserted ID
     */
    public function lastInsertId(?string $name = null): string
    {
        return $this->getNativePdo()->lastInsertId($name);
    }
    
    /**
     * Insert data into a table.
     *
     * @param string $table The table name
     * @param array $data The data to insert
     * @param bool $returnStatement Whether to return the PDOStatement instead of row count
     * @return int|\PDOStatement|null The number of affected rows, PDOStatement, or null on failure
     */
    public function insert(string $table, array $data, bool $returnStatement = false)
    {
        return $this->insert->execute($table, $data, $returnStatement);
    }
    
    /**
     * Get the Insert model instance.
     *
     * @return \FoxQL\Models\Insert The Insert model instance
     */
    public function getInsert(): Insert
    {
        return $this->insert;
    }
    
    /**
     * Create a raw SQL expression.
     *
     * @param string $expression The raw SQL expression
     * @return \FoxQL\Core\Raw The Raw instance
     */
    public function raw(string $expression): Raw
    {
        return new Raw($expression);
    }
    
    /**
     * Select data from a table.
     *
     * @param string $table The table name
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions (optional)
     * @return array|null The selected data or null on failure
     */
    public function select(string $table, $columns, ?array $where = null): ?array
    {
        return $this->select->execute($table, $columns, $where);
    }
    
    /**
     * Select data from a table with join support.
     *
     * @param string $table The table name
     * @param array $join The join conditions
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions (optional)
     * @return array|null The selected data or null on failure
     */
    public function selectJoin(string $table, array $join, $columns, ?array $where = null): ?array
    {
        return $this->select->executeJoin($table, $join, $columns, $where);
    }
    
    /**
     * Select a single row from a table.
     *
     * @param string $table The table name
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions (optional)
     * @return array|null The selected row or null if not found or on failure
     */
    public function selectOne(string $table, $columns, ?array $where = null): ?array
    {
        return $this->select->executeOne($table, $columns, $where);
    }
    
    /**
     * Select a single value from a table.
     *
     * @param string $table The table name
     * @param string $column The column to select
     * @param array|null $where The WHERE clause conditions (optional)
     * @return mixed|null The selected value or null if not found or on failure
     */
    public function selectValue(string $table, string $column, ?array $where = null)
    {
        return $this->select->executeValue($table, $column, $where);
    }
    
    /**
     * Select data with a callback for each row.
     *
     * @param string $table The table name
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions
     * @param callable $callback The callback function to execute for each row
     * @return bool Whether the operation was successful
     */
    public function selectCallback(string $table, $columns, ?array $where, callable $callback): bool
    {
        return $this->select->executeCallback($table, $columns, $where, $callback);
    }
    
    /**
     * Get the Select model instance.
     *
     * @return \FoxQL\Models\Select The Select model instance
     */
    public function getSelect(): Select
    {
        return $this->select;
    }
    
    /**
     * Update data in a table.
     *
     * @param string $table The table name
     * @param array $data The data to update
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return \PDOStatement|null The PDOStatement or null on failure
     */
    public function update(string $table, array $data, ?array $where = null): ?PDOStatement
    {
        return $this->update->execute($table, $data, $where);
    }
    
    /**
     * Get the Update model instance.
     *
     * @return \FoxQL\Models\Update The Update model instance
     */
    public function getUpdate(): Update
    {
        return $this->update;
    }
    
    /**
     * Delete data from a table.
     *
     * @param string $table The table name
     * @param array|null $where The WHERE clause to filter records
     * @return \PDOStatement|null The PDOStatement or null on failure
     */
    public function delete(string $table, ?array $where = null): ?PDOStatement
    {
        return $this->delete->execute($table, $where);
    }
    
    /**
     * Get the Delete model instance.
     *
     * @return \FoxQL\Models\Delete The Delete model instance
     */
    public function getDelete(): Delete
    {
        return $this->delete;
    }
    
    /**
     * Replace old data with new data in a table.
     *
     * @param string $table The table name
     * @param array $columns The columns with values to replace
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return \PDOStatement|null The PDOStatement or null on failure
     */
    public function replace(string $table, array $columns, ?array $where = null): ?PDOStatement
    {
        return $this->replace->execute($table, $columns, $where);
    }
    
    /**
     * Get the Replace model instance.
     *
     * @return \FoxQL\Models\Replace The Replace model instance
     */
    public function getReplace(): Replace
    {
        return $this->replace;
    }
    
    /**
     * Get a single record from the table.
     *
     * @param string $table The table name
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions
     * @return mixed The record data
     */
    public function get(string $table, $columns, ?array $where = null)
    {
        try {
            return $this->get->execute($table, $columns, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Get a single record from the table with join support.
     *
     * @param string $table The table name
     * @param array $join The join conditions
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions
     * @return mixed The record data
     */
    public function getJoin(string $table, array $join, $columns, ?array $where = null)
    {
        try {
            return $this->get->executeJoin($table, $join, $columns, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Get the Get model instance.
     *
     * @return \FoxQL\Models\Get The Get model instance
     */
    public function getGet(): Get
    {
        return $this->get;
    }
    
    /**
     * Determine whether the target data exists in the table.
     *
     * @param string $table The table name
     * @param array $where The WHERE clause to filter records
     * @return bool Boolean value for founded data
     */
    public function has(string $table, array $where): bool
    {
        try {
            return $this->has->execute($table, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Determine whether the target data exists in the table with join support.
     *
     * @param string $table The table name
     * @param array $join Table relativity for tables
     * @param array $where The WHERE clause to filter records
     * @return bool Boolean value for founded data
     */
    public function hasJoin(string $table, array $join, array $where): bool
    {
        try {
            return $this->has->executeJoin($table, $join, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Get the Has model instance.
     *
     * @return \FoxQL\Models\Has The Has model instance
     */
    public function getHas(): Has
    {
        return $this->has;
    }
    
    /**
     * Fetch data from the table randomly.
     *
     * @param string $table The table name
     * @param array|string $columns The target columns of data will be fetched
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return array|null Random data from the table
     */
    public function rand(string $table, $columns, ?array $where = null): ?array
    {
        try {
            return $this->rand->execute($table, $columns, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Fetch data from the table randomly with join support.
     *
     * @param string $table The table name
     * @param array $join Table relativity for tables
     * @param array|string $columns The target columns of data will be fetched
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return array|null Random data from the table
     */
    public function randJoin(string $table, array $join, $columns, ?array $where = null): ?array
    {
        try {
            return $this->rand->executeJoin($table, $join, $columns, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Get the Rand model instance.
     *
     * @return \FoxQL\Models\Rand The Rand model instance
     */
    public function getRand(): Rand
    {
        return $this->rand;
    }
    
    /**
     * Count the number of rows from the table.
     *
     * @param string $table The table name
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return int The number of rows
     */
    public function count(string $table, ?array $where = null): int
    {
        try {
            return $this->count->execute($table, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return 0;
        }
    }
    
    /**
     * Count the number of rows from the table with join support.
     *
     * @param string $table The table name
     * @param array $join Table relativity for tables
     * @param string $column The target column will be counted
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return int The number of rows
     */
    public function countJoin(string $table, array $join, string $column, ?array $where = null): int
    {
        try {
            return $this->count->executeJoin($table, $join, $column, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return 0;
        }
    }
    
    /**
     * Get the Count model instance.
     *
     * @return \FoxQL\Models\Count The Count model instance
     */
    public function getCount(): Count
    {
        return $this->count;
    }
    
    /**
     * Get the maximum value of the column.
     *
     * @param string $table The table name
     * @param string $column The target column will be calculated
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return string|null The maximum value of the column
     */
    public function max(string $table, string $column, ?array $where = null): ?string
    {
        try {
            return $this->max->execute($table, $column, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Get the maximum value of the column with join support.
     *
     * @param string $table The table name
     * @param array $join Table relativity for tables
     * @param string $column The target column will be calculated
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return string|null The maximum value of the column
     */
    public function maxJoin(string $table, array $join, string $column, ?array $where = null): ?string
    {
        try {
            return $this->max->executeJoin($table, $join, $column, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Get the Max model instance.
     *
     * @return \FoxQL\Models\Max The Max model instance
     */
    public function getMax(): Max
    {
        return $this->max;
    }
    
    /**
     * Get the minimum value of the column.
     *
     * @param string $table The table name
     * @param string $column The target column will be calculated
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return string|null The minimum value of the column
     */
    public function min(string $table, string $column, ?array $where = null): ?string
    {
        try {
            return $this->min->execute($table, $column, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Get the minimum value of the column with join support.
     *
     * @param string $table The table name
     * @param array $join Table relativity for tables
     * @param string $column The target column will be calculated
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return string|null The minimum value of the column
     */
    public function minJoin(string $table, array $join, string $column, ?array $where = null): ?string
    {
        try {
            return $this->min->executeJoin($table, $join, $column, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Get the Min model instance.
     *
     * @return \FoxQL\Models\Min The Min model instance
     */
    public function getMin(): Min
    {
        return $this->min;
    }
    
    /**
     * Calculate the average value of the column.
     *
     * @param string $table The table name
     * @param string $column The target column will be calculated
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return string|null The average value of the column
     */
    public function avg(string $table, string $column, ?array $where = null): ?string
    {
        try {
            return $this->avg->execute($table, $column, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Calculate the average value of the column with join support.
     *
     * @param string $table The table name
     * @param array $join Table relativity for tables
     * @param string $column The target column will be calculated
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return string|null The average value of the column
     */
    public function avgJoin(string $table, array $join, string $column, ?array $where = null): ?string
    {
        try {
            return $this->avg->executeJoin($table, $join, $column, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Get the Avg model instance.
     *
     * @return \FoxQL\Models\Avg The Avg model instance
     */
    public function getAvg(): Avg
    {
        return $this->avg;
    }
    
    /**
     * Calculate the sum of values in the column.
     *
     * @param string $table The table name
     * @param string $column The target column will be calculated
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return string|null The sum of values in the column
     */
    public function sum(string $table, string $column, ?array $where = null): ?string
    {
        try {
            return $this->sum->execute($table, $column, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Calculate the sum of values in the column with join support.
     *
     * @param string $table The table name
     * @param array $join Table relativity for tables
     * @param string $column The target column will be calculated
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return string|null The sum of values in the column
     */
    public function sumJoin(string $table, array $join, string $column, ?array $where = null): ?string
    {
        try {
            return $this->sum->executeJoin($table, $join, $column, $where);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Get the Sum model instance.
     *
     * @return \FoxQL\Models\Sum The Sum model instance
     */
    public function getSum(): Sum
    {
        return $this->sum;
    }
    
    /**
     * Create a new table in the database.
     *
     * Example usage:
     * ```php
     * $database->create("account", [
     *     "id" => [
     *         "INT",
     *         "NOT NULL",
     *         "AUTO_INCREMENT",
     *         "PRIMARY KEY"
     *     ],
     *     "first_name" => [
     *         "VARCHAR(30)",
     *         "NOT NULL"
     *     ]
     * ]);
     * ```
     *
     * Advanced usage with table options:
     * ```php
     * $database->create("account", [
     *     "id" => [
     *         "INT",
     *         "NOT NULL",
     *         "AUTO_INCREMENT"
     *     ],
     *     "email" => [
     *         "VARCHAR(70)",
     *         "NOT NULL",
     *         "UNIQUE"
     *     ],
     *     "PRIMARY KEY (<id>)"
     * ], [
     *     "ENGINE" => "MyISAM",
     *     "AUTO_INCREMENT" => 200
     * ]);
     * ```
     *
     * @param string $table The table name
     * @param array $columns The column definitions
     * @param array|string|null $options Additional table options (optional)
     * @return PDOStatement|null The PDO statement or null on failure
     */
    public function create(string $table, array $columns, $options = null): ?PDOStatement
    {
        try {
            return $this->create->execute($table, $columns, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Get the Create model instance.
     *
     * @return \FoxQL\Models\Create The Create model instance
     */
    public function getCreate(): Create
    {
        return $this->create;
    }
    
    /**
     * Drop a table from the database.
     *
     * Example usage:
     * ```php
     * $database->drop("account");
     * ```
     *
     * @param string $table The table name
     * @return PDOStatement|null The PDO statement or null on failure
     */
    public function drop(string $table): ?PDOStatement
    {
        try {
            return $this->drop->execute($table);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Get the Drop model instance.
     *
     * @return \FoxQL\Models\Drop The Drop model instance
     */
    public function getDrop(): Drop
    {
        return $this->drop;
    }
    
    /**
     * Get the ID of the last inserted row.
     *
     * @param string|null $name The name of the sequence object (required for PostgreSQL)
     * @return string|null The last inserted ID
     */
    public function id(?string $name = null): ?string
    {
        try {
            return $this->pdo->getPdo()->lastInsertId($name);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Start a transaction and execute a callback function within the transaction.
     *
     * The transaction will be automatically committed if the callback returns a non-false value,
     * or rolled back if the callback returns false or throws an exception.
     *
     * Example usage:
     * ```php
     * $database->action(function($database) {
     *     $database->insert("account", [
     *         "name" => "foo",
     *         "email" => "bar@abc.com"
     *     ]);
     *     
     *     $database->delete("account", [
     *         "user_id" => 2312
     *     ]);
     *     
     *     // If you found something wrong, just return a false value to roll back the whole transaction.
     *     if ($database->has("post", ["user_id" => 2312])) {
     *         return false;
     *     }
     * });
     * ```
     *
     * Accessing data outside of action:
     * ```php
     * $result = "";
     * 
     * $database->action(function($database) use (&$result) {
     *     $database->insert("account", [
     *         "user_name" => "foo"
     *     ]);
     *     
     *     $newId = $database->id();
     *     
     *     $result = "Account is created, and the id is {$newId}.";
     * });
     * 
     * echo $result;
     * ```
     *
     * @param callable $callback The transaction callback function
     * @return bool True if the transaction was committed, false if it was rolled back
     * @throws \PDOException If the database doesn't support transactions or an error occurs
     */
    public function action(callable $callback): bool
    {
        try {
            // Start transaction
            $this->pdo->getPdo()->beginTransaction();
            
            // Execute the callback
            $result = $callback($this);
            
            // Commit or rollback based on the callback result
            if ($result === false) {
                $this->pdo->getPdo()->rollBack();
                return false;
            } else {
                $this->pdo->getPdo()->commit();
                return true;
            }
        } catch (\Exception $e) {
            // Rollback on exception
            if ($this->pdo->getPdo()->inTransaction()) {
                $this->pdo->getPdo()->rollBack();
            }
            
            // Store the error message
            $this->error = $e->getMessage();
            
            // Re-throw the exception
            throw $e;
        }
    }
}