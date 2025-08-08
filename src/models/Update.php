<?php

declare(strict_types=1);

namespace FoxQL\Models;

use FoxQL\Core\Pdo;
use FoxQL\Core\Raw;
use PDO as NativePDO;
use PDOStatement;
use PDOException;
use InvalidArgumentException;
use DateTime;
use JsonSerializable;

/**
 * Update class for FoxQL
 * 
 * Provides functionality for updating data in database tables
 * with support for:
 * - Array serialization
 * - Type auto-detection
 * - Mathematical operations ([+], [-], [*], [/])
 * - PDOStatement
 * - SQL functions
 */
class Update
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
     * Create a new Update instance.
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
     * Update data in a table.
     *
     * @param string $table The table name
     * @param array $data The data to update
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return \PDOStatement|null The PDOStatement or null on failure
     */
    public function execute(string $table, array $data, ?array $where = null): ?PDOStatement
    {
        if (empty($data)) {
            $this->error = 'No data provided for update.';
            return null;
        }
        
        // Process data for type auto-detection, array serialization, and mathematical operations
        $processedData = $this->processData($data);
        
        $setStatements = [];
        $values = [];
        
        // Build SET statements
        foreach ($processedData as $key => $value) {
            // Check for mathematical operations
            if (is_string($key) && preg_match('/^(.+)\[(\+|\-|\*|\/)\]$/', $key, $matches)) {
                $column = $matches[1];
                $operator = $matches[2];
                
                if ($value instanceof Raw) {
                    $setStatements[] = "{$column} = {$column} {$operator} {$value->getValue()}";
                } else {
                    $setStatements[] = "{$column} = {$column} {$operator} ?";
                    $values[] = $value;
                }
            } 
            // Handle regular updates
            else {
                if ($value instanceof Raw) {
                    $setStatements[] = "{$key} = {$value->getValue()}";
                } else {
                    $setStatements[] = "{$key} = ?";
                    $values[] = $value;
                }
            }
        }
        
        // Build WHERE clause
        $whereClause = '';
        if (!empty($where)) {
            $whereResult = $this->buildWhere($where);
            $whereClause = ' WHERE ' . $whereResult['sql'];
            $values = array_merge($values, $whereResult['values']);
        }
        
        $query = sprintf(
            'UPDATE %s%s SET %s%s',
            $this->prefix,
            $table,
            implode(', ', $setStatements),
            $whereClause
        );
        
        $this->statement = $this->pdo->execute($query, $values);
        
        if (!$this->statement) {
            $this->error = $this->pdo->getError();
            return null;
        }
        
        return $this->statement;
    }
    
    /**
     * Process data for update, handling type auto-detection, array serialization, and mathematical operations.
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
            if ($value instanceof Raw) {
                $processed[$key] = $value;
                continue;
            }
            
            // Handle arrays (serialize to JSON)
            if (is_array($value)) {
                // Check for JSON flag
                if (is_string($key) && preg_match('/^(.+)\s+\[JSON\]$/', $key, $matches)) {
                    $processed[$matches[1]] = json_encode($value);
                } else {
                    $processed[$key] = json_encode($value);
                }
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
     * Build a WHERE clause for the query.
     *
     * @param array $where The WHERE conditions
     * @return array The SQL and values for the WHERE clause
     */
    protected function buildWhere(array $where): array
    {
        $conditions = [];
        $values = [];
        
        foreach ($where as $column => $value) {
            // Handle Raw SQL expressions
            if ($value instanceof Raw) {
                $conditions[] = "{$column} = {$value->getValue()}";
                continue;
            }
            
            // Handle operators in column names
            if (is_string($column) && preg_match('/^(.+)\[(\>|\<|\>=|\<=|\<\>|\!\=|LIKE|NOT LIKE|IN|NOT IN)\]$/', $column, $matches)) {
                $field = $matches[1];
                $operator = strtoupper($matches[2]);
                
                if ($operator === 'IN' || $operator === 'NOT IN') {
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    
                    $placeholders = array_fill(0, count($value), '?');
                    $conditions[] = "{$field} {$operator} (" . implode(', ', $placeholders) . ")";
                    $values = array_merge($values, array_values($value));
                } else {
                    $conditions[] = "{$field} {$operator} ?";
                    $values[] = $value;
                }
            }
            // Handle simple equality
            else {
                if (is_null($value)) {
                    $conditions[] = "{$column} IS NULL";
                } else {
                    $conditions[] = "{$column} = ?";
                    $values[] = $value;
                }
            }
        }
        
        return [
            'sql' => implode(' AND ', $conditions),
            'values' => $values
        ];
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
     * Get the last PDO statement.
     *
     * @return \PDOStatement|null The last PDO statement
     */
    public function getStatement(): ?PDOStatement
    {
        return $this->statement;
    }
}