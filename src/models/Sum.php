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
 * Sum class for FoxQL
 * 
 * Provides functionality for calculating the sum of values in a column in database tables
 * with support for:
 * - Simple sum calculation
 * - WHERE clauses
 * - Table joins
 */
class Sum
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
     * Create a new Sum instance.
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
     * Calculate the sum of values in a column from the table.
     *
     * @param string $table The table name
     * @param string $column The target column to calculate the sum from
     * @param array|null $where The WHERE clause conditions (optional)
     * @return string|null The sum value or null if no records found
     */
    public function execute(string $table, string $column, ?array $where = null): ?string
    {
        // Use the Select model to calculate the sum value
        $select = new Select($this->pdo, $this->prefix);
        
        // Set up the sum query
        $result = $select->execute($table, ["SUM($column)" => 'sum_value'], $where);
        
        // Return the sum value
        return isset($result[0]['sum_value']) ? $result[0]['sum_value'] : null;
    }

    /**
     * Calculate the sum of values in a column from the table with join support.
     *
     * @param string $table The table name
     * @param array $join The join conditions
     * @param string $column The target column to calculate the sum from
     * @param array|null $where The WHERE clause conditions (optional)
     * @return string|null The sum value or null if no records found
     */
    public function executeJoin(string $table, array $join, string $column, ?array $where = null): ?string
    {
        // Use the Select model to calculate the sum value with join support
        $select = new Select($this->pdo, $this->prefix);
        
        // Set up the sum query with the specified column
        $result = $select->executeJoin($table, $join, ["SUM($column)" => 'sum_value'], $where);
        
        // Return the sum value
        return isset($result[0]['sum_value']) ? $result[0]['sum_value'] : null;
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