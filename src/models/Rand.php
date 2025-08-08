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
 * Rand class for FoxQL
 * 
 * Provides functionality for fetching random data from database tables
 * with support for:
 * - Column selection
 * - WHERE clauses
 * - Table joins
 */
class Rand
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
     * Create a new Rand instance.
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
     * Fetch random data from a table.
     *
     * @param string $table The table name
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions (optional)
     * @return array|null The selected data or null on failure
     */
    public function execute(string $table, $columns, ?array $where = null): ?array
    {
        // Add ORDER BY RANDOM to the where conditions
        if ($where === null) {
            $where = ['ORDER' => 'RANDOM()'];
        } else {
            $where['ORDER'] = 'RANDOM()';
        }
        
        // Use the Select model to fetch the data with random ordering
        $select = new Select($this->pdo, $this->prefix);
        return $select->execute($table, $columns, $where);
    }

    /**
     * Fetch random data from a table with join support.
     *
     * @param string $table The table name
     * @param array $join The join conditions
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions (optional)
     * @return array|null The selected data or null on failure
     */
    public function executeJoin(string $table, array $join, $columns, ?array $where = null): ?array
    {
        // Add ORDER BY RANDOM to the where conditions
        if ($where === null) {
            $where = ['ORDER' => 'RANDOM()'];
        } else {
            $where['ORDER'] = 'RANDOM()';
        }
        
        // Use the Select model to fetch the data with random ordering and join support
        $select = new Select($this->pdo, $this->prefix);
        return $select->executeJoin($table, $join, $columns, $where);
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