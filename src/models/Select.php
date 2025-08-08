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
 * Select class for FoxQL
 * 
 * Provides functionality for selecting data from database tables
 * with support for:
 * - Column selection
 * - WHERE clauses
 * - Table joins
 * - Distinct selection
 * - Data mapping
 * - Index mapping
 * - Data type declaration
 * - Aliasing
 */
class Select
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
     * The last PDO statement.
     *
     * @var \PDOStatement|null
     */
    protected $statement = null;

    /**
     * Create a new Select instance.
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
     * Select data from a table.
     *
     * @param string $table The table name
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions
     * @return array|null The selected data or null on failure
     */
    public function execute(string $table, $columns, ?array $where = null): ?array
    {
        return $this->executeSelect($table, null, $columns, $where);
    }

    /**
     * Select data from a table with join support.
     *
     * @param string $table The table name
     * @param array $join The join conditions
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions
     * @return array|null The selected data or null on failure
     */
    public function executeJoin(string $table, array $join, $columns, ?array $where = null): ?array
    {
        return $this->executeSelect($table, $join, $columns, $where);
    }

    /**
     * Execute a select query.
     *
     * @param string $table The table name
     * @param array|null $join The join conditions
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions
     * @return array|null The selected data or null on failure
     */
    protected function executeSelect(string $table, ?array $join, $columns, ?array $where): ?array
    {
        // Build the query
        list($query, $params) = $this->buildSelectQuery($table, $join, $columns, $where);
        
        // Execute the query
        $this->statement = $this->pdo->execute($query, $params);
        
        if (!$this->statement) {
            $this->error = $this->pdo->getError();
            return null;
        }
        
        return $this->statement->fetchAll(NativePDO::FETCH_ASSOC);
    }

    /**
     * Build a select query.
     *
     * @param string $table The table name
     * @param array|null $join The join conditions
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions
     * @return array An array containing the query string and parameters
     */
    protected function buildSelectQuery(string $table, ?array $join, $columns, ?array $where): array
    {
        $query = ['SELECT'];
        $params = [];
        
        // Process columns
        $query[] = $this->buildColumns($columns);
        
        // Add FROM clause
        $query[] = 'FROM ' . $this->prefix . $table;
        
        // Process joins if provided
        if ($join) {
            $joinClauses = $this->buildJoin($join);
            if (!empty($joinClauses)) {
                $query[] = $joinClauses;
            }
        }
        
        // Process WHERE clause if provided
        if ($where) {
            list($whereClause, $whereParams) = $this->buildWhere($where);
            if (!empty($whereClause)) {
                $query[] = 'WHERE ' . $whereClause;
                $params = array_merge($params, $whereParams);
            }
        }
        
        return [implode(' ', $query), $params];
    }

    /**
     * Build the columns part of the query.
     *
     * @param array|string $columns The columns to select
     * @return string The columns clause
     */
    protected function buildColumns($columns): string
    {
        if ($columns === '*' || (is_array($columns) && in_array('*', $columns))) {
            return '*';
        }
        
        if (is_string($columns)) {
            return $columns;
        }
        
        $columnList = [];
        
        foreach ($columns as $key => $value) {
            // Handle distinct columns (prefixed with @)
            if (is_string($value) && strpos($value, '@') === 0) {
                $columnList[] = 'DISTINCT ' . substr($value, 1);
                continue;
            }
            
            // Handle column aliases
            if (is_string($key) && !is_numeric($key)) {
                if (is_array($value)) {
                    // Handle data mapping
                    $columnList[] = $this->buildDataMapping($key, $value);
                } else {
                    // Handle simple alias
                    $columnList[] = $value . ' AS ' . $key;
                }
            } else {
                // Regular column
                $columnList[] = $value;
            }
        }
        
        return implode(', ', $columnList);
    }

    /**
     * Build data mapping for columns.
     *
     * @param string $alias The column alias
     * @param array $mapping The data mapping configuration
     * @return string The data mapping clause
     */
    protected function buildDataMapping(string $alias, array $mapping): string
    {
        // This is a simplified implementation
        // A more complex implementation would handle nested mappings
        return implode(', ', $mapping) . ' AS ' . $alias;
    }

    /**
     * Build the JOIN part of the query.
     *
     * @param array $join The join conditions
     * @return string The JOIN clause
     */
    protected function buildJoin(array $join): string
    {
        $joinClauses = [];
        
        foreach ($join as $table => $conditions) {
            $joinType = 'INNER JOIN';
            $constraint = '';
            
            // Handle join type
            if (isset($conditions[0])) {
                $joinType = strtoupper($conditions[0]) . ' JOIN';
                unset($conditions[0]);
            }
            
            // Handle join conditions
            if (isset($conditions[1])) {
                $constraint = $conditions[1];
                unset($conditions[1]);
            }
            
            // Build the join clause
            $joinClauses[] = $joinType . ' ' . $this->prefix . $table . 
                             ($constraint ? ' ON ' . $constraint : '');
        }
        
        return implode(' ', $joinClauses);
    }

    /**
     * Build the WHERE part of the query.
     *
     * @param array $where The WHERE conditions
     * @return array An array containing the WHERE clause and parameters
     */
    protected function buildWhere(array $where): array
    {
        $conditions = [];
        $params = [];
        
        foreach ($where as $column => $value) {
            // Handle raw expressions
            if ($value instanceof Raw) {
                $conditions[] = $column . ' = ' . $value->getValue();
                continue;
            }
            
            // Handle operators in column names (e.g., "column[>]")
            if (preg_match('/\[(.*?)\]$/', $column, $match)) {
                $operator = $match[1];
                $realColumn = str_replace('[' . $operator . ']', '', $column);
                
                switch ($operator) {
                    case '>':
                    case '<':
                    case '>=':
                    case '<=':
                    case '!=':
                    case '<>':
                        $conditions[] = $realColumn . ' ' . $operator . ' ?';
                        $params[] = $value;
                        break;
                    case 'LIKE':
                        $conditions[] = $realColumn . ' LIKE ?';
                        $params[] = $value;
                        break;
                    case 'NOT LIKE':
                        $conditions[] = $realColumn . ' NOT LIKE ?';
                        $params[] = $value;
                        break;
                    case 'IN':
                        $placeholders = array_fill(0, count($value), '?');
                        $conditions[] = $realColumn . ' IN (' . implode(', ', $placeholders) . ')';
                        $params = array_merge($params, $value);
                        break;
                    case 'NOT IN':
                        $placeholders = array_fill(0, count($value), '?');
                        $conditions[] = $realColumn . ' NOT IN (' . implode(', ', $placeholders) . ')';
                        $params = array_merge($params, $value);
                        break;
                    case 'BETWEEN':
                        $conditions[] = $realColumn . ' BETWEEN ? AND ?';
                        $params[] = $value[0];
                        $params[] = $value[1];
                        break;
                    case 'NOT BETWEEN':
                        $conditions[] = $realColumn . ' NOT BETWEEN ? AND ?';
                        $params[] = $value[0];
                        $params[] = $value[1];
                        break;
                }
            } else {
                // Handle simple equality
                if (is_null($value)) {
                    $conditions[] = $column . ' IS NULL';
                } else if (is_array($value)) {
                    // Handle IN operator for arrays
                    $placeholders = array_fill(0, count($value), '?');
                    $conditions[] = $column . ' IN (' . implode(', ', $placeholders) . ')';
                    $params = array_merge($params, $value);
                } else {
                    $conditions[] = $column . ' = ?';
                    $params[] = $value;
                }
            }
        }
        
        return [implode(' AND ', $conditions), $params];
    }

    /**
     * Select a single row from a table.
     *
     * @param string $table The table name
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions
     * @return array|null The selected row or null if not found or on failure
     */
    public function executeOne(string $table, $columns, ?array $where = null): ?array
    {
        $data = $this->execute($table, $columns, $where);
        
        if (!$data) {
            return null;
        }
        
        return reset($data) ?: null;
    }

    /**
     * Select a single row from a table with join support.
     *
     * @param string $table The table name
     * @param array $join The join conditions
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions
     * @return array|null The selected row or null if not found or on failure
     */
    public function executeJoinOne(string $table, array $join, $columns, ?array $where = null): ?array
    {
        $data = $this->executeJoin($table, $join, $columns, $where);
        
        if (!$data) {
            return null;
        }
        
        return reset($data) ?: null;
    }

    /**
     * Select a single value from a table.
     *
     * @param string $table The table name
     * @param string $column The column to select
     * @param array|null $where The WHERE clause conditions
     * @return mixed|null The selected value or null if not found or on failure
     */
    public function executeValue(string $table, string $column, ?array $where = null)
    {
        $data = $this->executeOne($table, [$column], $where);
        
        if (!$data) {
            return null;
        }
        
        return reset($data) ?: null;
    }

    /**
     * Select data with a callback for each row.
     *
     * @param string $table The table name
     * @param array|string $columns The columns to select
     * @param array|null $where The WHERE clause conditions
     * @param callable $callback The callback function to execute for each row
     * @return bool Whether the operation was successful
     */
    public function executeCallback(string $table, $columns, ?array $where, callable $callback): bool
    {
        // Build the query
        list($query, $params) = $this->buildSelectQuery($table, null, $columns, $where);
        
        // Execute the query
        $this->statement = $this->pdo->execute($query, $params);
        
        if (!$this->statement) {
            $this->error = $this->pdo->getError();
            return false;
        }
        
        // Process each row with the callback
        while ($row = $this->statement->fetch(NativePDO::FETCH_ASSOC)) {
            $callback($row);
        }
        
        return true;
    }

    /**
     * Get the last error message.
     *
     * @return string|null The last error message
     */
    public function getError(): ?string
    {
        return $this->error;
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