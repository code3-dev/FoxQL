<?php

declare(strict_types=1);

namespace FoxQL\Models;

use FoxQL\Core\Pdo;
use PDO as NativePDO;
use PDOStatement;
use PDOException;
use InvalidArgumentException;
use DateTime;
use JsonSerializable;

/**
 * Insert class for FoxQL
 * 
 * Provides functionality for inserting data into database tables
 * with support for:
 * - Last insert ID
 * - Array serialization
 * - Type auto-detection
 * - Multi-insertion
 * - PDOStatement
 * - SQL functions
 */
class Insert
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
     * Create a new Insert instance.
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
     * The last PDO statement.
     *
     * @var \PDOStatement|null
     */
    protected $statement = null;
    
    /**
     * Insert data into a table.
     *
     * @param string $table The table name
     * @param array $data The data to insert
     * @param bool $returnStatement Whether to return the PDOStatement instead of row count
     * @return int|\PDOStatement|null The number of affected rows, PDOStatement, or null on failure
     */
    public function execute(string $table, array $data, bool $returnStatement = false)
    {
        if (empty($data)) {
            $this->error = 'No data provided for insertion.';
            return null;
        }
        
        // Check if this is a multi-insertion (array of arrays)
        $isMultiInsert = $this->isMultiInsert($data);
        
        if ($isMultiInsert) {
            return $this->executeMultiInsert($table, $data, $returnStatement);
        }
        
        // Process data for type auto-detection and array serialization
        $processedData = $this->processData($data);
        
        $keys = array_keys($processedData);
        $values = array_values($processedData);
        $placeholders = [];
        
        // Handle raw SQL expressions
        foreach ($values as $i => $value) {
            if ($value instanceof \FoxQL\Core\Raw) {
                $placeholders[] = $value->getValue();
                unset($values[$i]);
            } else {
                $placeholders[] = '?';
            }
        }
        
        // Reindex values array after removing Raw instances
        $values = array_values($values);
        
        $query = sprintf(
            'INSERT INTO %s%s (%s) VALUES (%s)',
            $this->prefix,
            $table,
            implode(', ', $keys),
            implode(', ', $placeholders)
        );
        
        $this->statement = $this->pdo->execute($query, $values);
        
        if (!$this->statement) {
            $this->error = $this->pdo->getError();
            return null;
        }
        
        return $returnStatement ? $this->statement : $this->statement->rowCount();
    }
    
    /**
     * Execute a multi-insert query.
     *
     * @param string $table The table name
     * @param array $data The data to insert (array of arrays)
     * @param bool $returnStatement Whether to return the PDOStatement instead of row count
     * @return int|\PDOStatement|null The number of affected rows, PDOStatement, or null on failure
     */
    protected function executeMultiInsert(string $table, array $data, bool $returnStatement = false)
    {
        if (empty($data)) {
            $this->error = 'No data provided for insertion.';
            return null;
        }
        
        // Get the keys from the first row
        $firstRow = reset($data);
        $keys = array_keys($firstRow);
        
        // Build placeholders for each row
        $allPlaceholders = [];
        $allValues = [];
        
        foreach ($data as $row) {
            // Ensure all rows have the same keys
            if (array_keys($row) !== $keys) {
                $this->error = 'All rows in multi-insert must have the same columns.';
                return null;
            }
            
            // Process data for type auto-detection and array serialization
            $processedRow = $this->processData($row);
            $rowPlaceholders = [];
            
            // Handle raw SQL expressions
            foreach ($processedRow as $value) {
                if ($value instanceof \FoxQL\Core\Raw) {
                    $rowPlaceholders[] = $value->getValue();
                } else {
                    $rowPlaceholders[] = '?';
                    $allValues[] = $value;
                }
            }
            
            $allPlaceholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }
        
        $query = sprintf(
            'INSERT INTO %s%s (%s) VALUES %s',
            $this->prefix,
            $table,
            implode(', ', $keys),
            implode(', ', $allPlaceholders)
        );
        
        $this->statement = $this->pdo->execute($query, $allValues);
        
        if (!$this->statement) {
            $this->error = $this->pdo->getError();
            return null;
        }
        
        return $returnStatement ? $this->statement : $this->statement->rowCount();
    }
    
    /**
     * Check if the data is for a multi-insert operation.
     *
     * @param array $data The data to check
     * @return bool Whether the data is for a multi-insert
     */
    protected function isMultiInsert(array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        
        $firstKey = array_key_first($data);
        return is_array($data[$firstKey]) && !empty($data[$firstKey]);
    }
    
    /**
     * Process data for insertion, handling type auto-detection and array serialization.
     *
     * @param array $data The data to process
     * @return array The processed data
     */
    protected function processData(array $data): array
    {
        $processed = [];
        
        foreach ($data as $key => $value) {
            // Handle null values
            if ($value === null) {
                $processed[$key] = null;
                continue;
            }
            
            // Handle Raw SQL expressions
            if ($value instanceof \FoxQL\Core\Raw) {
                $processed[$key] = $value;
                continue;
            }
            
            // Handle arrays (serialize to JSON)
            if (is_array($value)) {
                $processed[$key] = json_encode($value);
                continue;
            }
            
            // Handle objects that implement JsonSerializable
            if ($value instanceof JsonSerializable) {
                $processed[$key] = json_encode($value);
                continue;
            }
            
            // Handle DateTime objects
            if ($value instanceof DateTime) {
                $processed[$key] = $value->format('Y-m-d H:i:s');
                continue;
            }
            
            // Handle boolean values
            if (is_bool($value)) {
                $processed[$key] = (int) $value;
                continue;
            }
            
            // Default: keep as is
            $processed[$key] = $value;
        }
        
        return $processed;
    }

    /**
     * Get the last error message.
     *
     * @return string|null The last error message
     */
    public function getError(): ?string
    {
        return $this->error ?? $this->pdo->getError();
    }

    /**
     * Get the last error details.
     *
     * @return array|null The last error details
     */
    public function getErrorInfo(): ?array
    {
        return $this->pdo->getErrorInfo();
    }
    
    /**
     * Get the last insert ID.
     *
     * @param string|null $name Name of the sequence object from which the ID should be returned
     * @return string|false The last insert ID or false on failure
     */
    public function lastInsertId(?string $name = null)
    {
        if (!$this->pdo->isConnected()) {
            $this->error = 'No active database connection.';
            return false;
        }
        
        try {
            return $this->pdo->getPdo()->lastInsertId($name);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Get the last PDO statement.
     *
     * @return \PDOStatement|null The last PDO statement
     */
    public function getStatement(): ?PDOStatement
    {
        return $this->statement;
    }
}