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
 * Replace class for FoxQL
 * 
 * Provides functionality for replacing data in database tables
 * with support for:
 * - Column value replacement
 * - Array serialization
 * - Type auto-detection
 * - PDOStatement
 * - SQL functions
 */
class Replace
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
     * Create a new Replace instance.
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
     * Replace data in a table.
     *
     * @param string $table The table name
     * @param array $columns The columns with values to replace
     * @param array|null $where The WHERE clause to filter records (optional)
     * @return \PDOStatement|null The PDOStatement or null on failure
     */
    public function execute(string $table, array $columns, ?array $where = null): ?PDOStatement
    {
        if (empty($columns)) {
            $this->error = 'No columns provided for replace operation.';
            return null;
        }
        
        // Process the replacement data
        $setStatements = [];
        $values = [];
        
        // Build SET statements for replacements
        foreach ($columns as $column => $replacements) {
            if (!is_array($replacements)) {
                $this->error = "Replacements for column '{$column}' must be an array of old_value => new_value pairs.";
                return null;
            }
            
            foreach ($replacements as $oldValue => $newValue) {
                // Handle Raw SQL expressions for new value
                if ($newValue instanceof Raw) {
                    $setStatements[] = "{$column} = CASE WHEN {$column} = ? THEN {$newValue->getValue()} ELSE {$column} END";
                    $values[] = $oldValue;
                } else {
                    $setStatements[] = "{$column} = CASE WHEN {$column} = ? THEN ? ELSE {$column} END";
                    $values[] = $oldValue;
                    $values[] = $this->processValue($newValue);
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
     * Process a value for database storage.
     *
     * @param mixed $value The value to process
     * @return mixed The processed value
     */
    protected function processValue($value)
    {
        // Handle null values
        if ($value === null) {
            return null;
        }
        
        // Handle Raw SQL expressions
        if ($value instanceof Raw) {
            return $value;
        }
        
        // Handle arrays (serialize to JSON)
        if (is_array($value)) {
            return json_encode($value);
        }
        
        // Handle objects that implement JsonSerializable
        if ($value instanceof JsonSerializable) {
            return json_encode($value);
        }
        
        // Handle DateTime objects
        if ($value instanceof DateTime) {
            return $value->format('Y-m-d H:i:s');
        }
        
        // Handle boolean values
        if (is_bool($value)) {
            return (int) $value;
        }
        
        // Default: keep as is
        return $value;
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
            // Handle logical operators (AND, OR)
            if ($column === 'AND' || $column === 'OR') {
                if (!is_array($value)) {
                    continue;
                }
                
                $subConditions = [];
                foreach ($value as $subColumn => $subValue) {
                    $subResult = $this->buildWhere([$subColumn => $subValue]);
                    $subConditions[] = $subResult['sql'];
                    $values = array_merge($values, $subResult['values']);
                }
                
                if (!empty($subConditions)) {
                    $conditions[] = '(' . implode(' ' . $column . ' ', $subConditions) . ')';
                }
                
                continue;
            }
            
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