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
 * Delete class for FoxQL
 * 
 * Provides functionality for deleting data from database tables
 * with support for:
 * - WHERE clause conditions
 * - PDOStatement
 * - SQL functions
 */
class Delete
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
     * Create a new Delete instance.
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
     * Delete data from a table.
     *
     * @param string $table The table name
     * @param array|null $where The WHERE clause to filter records
     * @return \PDOStatement|null The PDOStatement or null on failure
     */
    public function execute(string $table, ?array $where = null): ?PDOStatement
    {
        // Build WHERE clause
        $whereClause = '';
        $values = [];
        
        if (!empty($where)) {
            $whereResult = $this->buildWhere($where);
            $whereClause = ' WHERE ' . $whereResult['sql'];
            $values = $whereResult['values'];
        }
        
        $query = sprintf(
            'DELETE FROM %s%s%s',
            $this->prefix,
            $table,
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