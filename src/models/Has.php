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
 * Has class for FoxQL
 * 
 * Provides functionality for checking if records exist in database tables
 * with support for:
 * - WHERE clauses
 * - Table joins
 */
class Has
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
     * Create a new Has instance.
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
     * Check if records exist in a table.
     *
     * @param string $table The table name
     * @param array $where The WHERE clause conditions
     * @return bool Whether records exist
     */
    public function execute(string $table, array $where): bool
    {
        // Use the Select model to count records
        $select = new Select($this->pdo, $this->prefix);
        $count = $select->executeValue($table, 'COUNT(*)', $where);
        
        // If count is null (error) or 0, return false
        return ($count !== null && $count > 0);
    }

    /**
     * Check if records exist in a table with join support.
     *
     * @param string $table The table name
     * @param array $join The join conditions
     * @param array $where The WHERE clause conditions
     * @return bool Whether records exist
     */
    public function executeJoin(string $table, array $join, array $where): bool
    {
        // Use the Select model to count records with join
        $select = new Select($this->pdo, $this->prefix);
        $data = $select->executeJoin($table, $join, 'COUNT(*) AS count', $where);
        
        // If data is null (error) or empty, return false
        if ($data === null || empty($data)) {
            return false;
        }
        
        // Get the count value from the first row
        $count = $data[0]['count'] ?? 0;
        
        return ($count > 0);
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