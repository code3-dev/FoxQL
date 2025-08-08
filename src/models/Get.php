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
 * Get class for FoxQL
 * 
 * Provides functionality for retrieving a single record from database tables
 * with support for:
 * - Column selection
 * - WHERE clauses
 * - Table joins
 * - Data mapping
 * - Index mapping
 * - Data type declaration
 * - Aliasing
 */
class Get
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
     * Create a new Get instance.
     *
     * @param \FoxQL\Core\Pdo $pdo
     * @param string $prefix
     */
    public function __construct(Pdo $pdo, string $prefix = '')
    {
        $this->pdo = $pdo;
        $this->prefix = $prefix;
    }

    /**
     * Get a single record from the table.
     *
     * @param string $table The table name
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions
     * @return mixed The record data
     */
    public function execute(string $table, $columns, ?array $where = null)
    {
        // If $columns is an array and $where is null, assume $columns contains WHERE conditions
        if (is_array($columns) && $where === null) {
            if (isset($columns['LIMIT'])) {
                // If LIMIT is already set, use it
                $where = $columns;
            } else {
                // Otherwise, set LIMIT to 1
                $where = $columns;
                $where['LIMIT'] = 1;
            }
            $columns = '*';
        } else if ($where === null) {
            // If $where is null, set default LIMIT to 1
            $where = ['LIMIT' => 1];
        } else {
            // Ensure LIMIT is set to 1
            $where['LIMIT'] = 1;
        }

        // Use the Select model's executeOne method to get a single record
        $select = new Select($this->pdo, $this->prefix);
        return $select->executeOne($table, $columns, $where);
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
    public function executeJoin(string $table, array $join, $columns, ?array $where = null)
    {
        // Ensure LIMIT is set to 1
        if ($where === null) {
            $where = ['LIMIT' => 1];
        } else {
            $where['LIMIT'] = 1;
        }

        // Use the Select model's executeJoinOne method to get a single record with join
        $select = new Select($this->pdo, $this->prefix);
        return $select->executeJoinOne($table, $join, $columns, $where);
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