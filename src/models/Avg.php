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
 * Avg class for FoxQL
 * 
 * Provides functionality for calculating the average value of a column in database tables
 * with support for:
 * - Simple average value calculation
 * - WHERE clauses
 * - Table joins
 */
class Avg
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
     * Create a new Avg instance.
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
     * Calculate the average value of a column from the table.
     *
     * @param string $table The table name
     * @param string $column The target column to calculate the average from
     * @param array|null $where The WHERE clause conditions (optional)
     * @return string|null The average value or null if no records found
     */
    public function execute(string $table, string $column, ?array $where = null): ?string
    {
        // Use the Select model to calculate the average value
        $select = new Select($this->pdo, $this->prefix);
        
        // Set up the avg query
        $result = $select->execute($table, ["AVG($column)" => 'avg_value'], $where);
        
        // Return the avg value
        return isset($result[0]['avg_value']) ? $result[0]['avg_value'] : null;
    }

    /**
     * Calculate the average value of a column from the table with join support.
     *
     * @param string $table The table name
     * @param array $join The join conditions
     * @param string $column The target column to calculate the average from
     * @param array|null $where The WHERE clause conditions (optional)
     * @return string|null The average value or null if no records found
     */
    public function executeJoin(string $table, array $join, string $column, ?array $where = null): ?string
    {
        // Use the Select model to calculate the average value with join support
        $select = new Select($this->pdo, $this->prefix);
        
        // Set up the avg query with the specified column
        $result = $select->executeJoin($table, $join, ["AVG($column)" => 'avg_value'], $where);
        
        // Return the avg value
        return isset($result[0]['avg_value']) ? $result[0]['avg_value'] : null;
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