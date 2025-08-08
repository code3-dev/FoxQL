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
 * Max class for FoxQL
 * 
 * Provides functionality for getting the maximum value of a column in database tables
 * with support for:
 * - Simple max value calculation
 * - WHERE clauses
 * - Table joins
 */
class Max
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
     * Create a new Max instance.
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
     * Get the maximum value of a column from the table.
     *
     * @param string $table The table name
     * @param string $column The target column to get the maximum value from
     * @param array|null $where The WHERE clause conditions (optional)
     * @return string|null The maximum value or null if no records found
     */
    public function execute(string $table, string $column, ?array $where = null): ?string
    {
        // Use the Select model to get the maximum value
        $select = new Select($this->pdo, $this->prefix);
        
        // Set up the max query
        $result = $select->execute($table, ["MAX($column)" => 'max_value'], $where);
        
        // Return the max value
        return isset($result[0]['max_value']) ? $result[0]['max_value'] : null;
    }

    /**
     * Get the maximum value of a column from the table with join support.
     *
     * @param string $table The table name
     * @param array $join The join conditions
     * @param string $column The target column to get the maximum value from
     * @param array|null $where The WHERE clause conditions (optional)
     * @return string|null The maximum value or null if no records found
     */
    public function executeJoin(string $table, array $join, string $column, ?array $where = null): ?string
    {
        // Use the Select model to get the maximum value with join support
        $select = new Select($this->pdo, $this->prefix);
        
        // Set up the max query with the specified column
        $result = $select->executeJoin($table, $join, ["MAX($column)" => 'max_value'], $where);
        
        // Return the max value
        return isset($result[0]['max_value']) ? $result[0]['max_value'] : null;
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