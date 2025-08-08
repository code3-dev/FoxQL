<?php

declare(strict_types=1);

namespace FoxQL\Models;

use FoxQL\Core\Pdo;
use FoxQL\Core\Raw;
use PDO as NativePDO;
use PDOStatement;
use PDOException;
use InvalidArgumentException;

/**
 * Count class for FoxQL
 * 
 * Provides functionality for counting records in database tables
 * with support for:
 * - Simple counting
 * - WHERE clauses
 * - Table joins
 * - Specific column counting
 */
class Count
{
    /**
     * The PDO wrapper instance.
     *
     * @var \FoxQL\Core\Pdo
     */
    protected $pdo;

    /**
     * The table prefix.
     *
     * @var string
     */
    protected $prefix;

    /**
     * The last error message.
     *
     * @var string|null
     */
    protected $error = null;

    /**
     * Create a new Count instance.
     *
     * @param \FoxQL\Core\Pdo $pdo The PDO wrapper instance
     * @param string $prefix The table prefix
     */
    public function __construct(Pdo $pdo, string $prefix = '')
    {
        $this->pdo = $pdo;
        $this->prefix = $prefix;
    }

    /**
     * Count the number of rows from the table.
     *
     * @param string $table The table name
     * @param array|null $where The WHERE clause conditions (optional)
     * @return int The number of rows
     */
    public function execute(string $table, ?array $where = null): int
    {
        // Use the Select model to count the data
        $select = new Select($this->pdo, $this->prefix);
        
        // Set up the count query
        $result = $select->execute($table, ['COUNT(*)' => 'count'], $where);
        
        // Return the count value
        return isset($result[0]['count']) ? (int) $result[0]['count'] : 0;
    }

    /**
     * Count the number of rows from the table with join support.
     *
     * @param string $table The table name
     * @param array $join The join conditions
     * @param string $column The target column to be counted
     * @param array|null $where The WHERE clause conditions (optional)
     * @return int The number of rows
     */
    public function executeJoin(string $table, array $join, string $column, ?array $where = null): int
    {
        // Use the Select model to count the data with join support
        $select = new Select($this->pdo, $this->prefix);
        
        // Set up the count query with the specified column
        $result = $select->executeJoin($table, $join, ["COUNT($column)" => 'count'], $where);
        
        // Return the count value
        return isset($result[0]['count']) ? (int) $result[0]['count'] : 0;
    }

    /**
     * Get the last error message.
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }
}