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
 * Create Model
 *
 * This class provides methods to create database tables.
 */
class Create
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
     * Create a new Create instance.
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
     * Create a new table in the database.
     *
     * @param string $table The table name
     * @param array $columns The column definitions
     * @param array|string|null $options Additional table options (optional)
     * @return PDOStatement|null The PDO statement or null on failure
     * @throws \PDOException If the query fails
     */
    public function execute(string $table, array $columns, $options = null): ?PDOStatement
    {
        // Apply table prefix
        $table = $this->prefix . $table;
        
        // Build column definitions
        $columnDefinitions = [];
        
        foreach ($columns as $name => $definition) {
            // If the definition is a raw string (for constraints, etc.)
            if (is_numeric($name)) {
                // Replace <column_name> with quoted column name
                $columnDefinitions[] = preg_replace_callback(
                    '/<([^>]+)>/',
                    function ($matches) {
                        return $this->quoteIdentifier($matches[1]);
                    },
                    $definition
                );
            } else {
                // Regular column definition
                $columnDefinitions[] = $this->quoteIdentifier($name) . ' ' . implode(' ', (array) $definition);
            }
        }
        
        // Build table options
        $tableOptions = '';
        
        if (!empty($options)) {
            if (is_array($options)) {
                $optionPairs = [];
                
                foreach ($options as $key => $value) {
                    $optionPairs[] = $key . ' = ' . $value;
                }
                
                $tableOptions = implode(', ', $optionPairs);
            } else {
                $tableOptions = $options;
            }
        }
        
        // Build the CREATE TABLE query
        $query = 'CREATE TABLE IF NOT EXISTS ' . $this->quoteIdentifier($table) . ' (' . 
                 implode(', ', $columnDefinitions) . ')';
        
        if (!empty($tableOptions)) {
            $query .= ' ' . $tableOptions;
        }
        
        // Execute the query
        try {
            return $this->pdo->execute($query);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            throw $e;
        }
    }
    
    /**
     * Quote an identifier according to the database type.
     *
     * @param string $identifier The identifier to quote
     * @return string The quoted identifier
     */
    protected function quoteIdentifier(string $identifier): string
    {
        $type = $this->pdo->getType();
        
        switch ($type) {
            case 'mysql':
                return '`' . str_replace('`', '``', $identifier) . '`';
                
            case 'mssql':
                return '[' . str_replace(']', ']]', $identifier) . ']';
                
            default:
                return '"' . str_replace('"', '""', $identifier) . '"';
        }
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
}