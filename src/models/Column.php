<?php

declare(strict_types=1);

namespace FoxQL\Models;

/**
 * Column Model
 *
 * This class provides a fluent interface for defining database column properties
 * with support for all database types: MySQL, PostgreSQL, SQLite, Sybase, Oracle, and MSSQL.
 */
class Column
{
    /**
     * The column name.
     *
     * @var string
     */
    protected $name;
    
    /**
     * The column type.
     *
     * @var string
     */
    protected $type;
    
    /**
     * The column modifiers.
     *
     * @var array
     */
    protected $modifiers = [];
    
    /**
     * The table name.
     *
     * @var string
     */
    protected $table;
    
    /**
     * The database type.
     *
     * @var string
     */
    protected $dbType;
    
    /**
     * Whether this column is for an alter operation.
     *
     * @var bool
     */
    protected $isAlter;
    
    /**
     * Create a new Column instance.
     *
     * @param string $name The column name
     * @param string $type The column type
     * @param array $modifiers The column modifiers
     * @param string $table The table name
     * @param string $dbType The database type
     * @param bool $isAlter Whether this column is for an alter operation
     */
    public function __construct(string $name, string $type, array $modifiers = [], string $table = '', string $dbType = '', bool $isAlter = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->modifiers = $modifiers;
        $this->table = $table;
        $this->dbType = strtolower($dbType);
        $this->isAlter = $isAlter;
    }
    
    /**
     * Get the column definition.
     *
     * @return array The column definition
     */
    public function getDefinition(): array
    {
        $definition = [$this->type];
        
        return array_merge($definition, $this->modifiers);
    }
    
    /**
     * Get the alter command for this column.
     *
     * @return string The alter command
     */
    public function getAlterCommand(): string
    {
        $definition = implode(' ', $this->getDefinition());
        
        return "ALTER TABLE {$this->table} ADD COLUMN {$this->name} {$definition}";
    }
    
    /**
     * Set the column as nullable.
     *
     * @param bool $value Whether the column is nullable
     * @return $this
     */
    public function nullable(bool $value = true): self
    {
        if ($value) {
            $this->modifiers[] = 'NULL';
        } else {
            $this->modifiers[] = 'NOT NULL';
        }
        
        return $this;
    }
    
    /**
     * Set the column as not nullable.
     *
     * @return $this
     */
    public function notNull(): self
    {
        return $this->nullable(false);
    }
    
    /**
     * Set the default value for the column.
     *
     * @param mixed $value The default value
     * @return $this
     */
    public function default($value): self
    {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_null($value)) {
            $value = 'NULL';
        } elseif (is_string($value) && !in_array(strtoupper($value), ['CURRENT_TIMESTAMP', 'NULL'])) {
            $value = "'{$value}'";
        }
        
        $this->modifiers[] = "DEFAULT {$value}";
        
        return $this;
    }
    
    /**
     * Set the column as unsigned (MySQL only).
     *
     * @return $this
     */
    public function unsigned(): self
    {
        if ($this->dbType === 'mysql') {
            $this->modifiers[] = 'UNSIGNED';
        }
        
        return $this;
    }
    
    /**
     * Set the column as a primary key.
     *
     * @return $this
     */
    public function primary(): self
    {
        $this->modifiers[] = 'PRIMARY KEY';
        
        return $this;
    }
    
    /**
     * Set the column as unique.
     *
     * @return $this
     */
    public function unique(): self
    {
        $this->modifiers[] = 'UNIQUE';
        
        return $this;
    }
    
    /**
     * Add a check constraint to the column.
     *
     * @param string $constraint The check constraint
     * @return $this
     */
    public function check(string $constraint): self
    {
        $this->modifiers[] = "CHECK ({$constraint})";
        
        return $this;
    }
    
    /**
     * Add a comment to the column (MySQL, PostgreSQL).
     *
     * @param string $comment The column comment
     * @return $this
     */
    public function comment(string $comment): self
    {
        if ($this->dbType === 'mysql') {
            $this->modifiers[] = "COMMENT '{$comment}'";
        } elseif ($this->dbType === 'pgsql' && $this->isAlter) {
            // For PostgreSQL, we need to add a separate command
            // This is handled in the migration class
        }
        
        return $this;
    }
    
    /**
     * Set the column to auto-increment.
     *
     * @return $this
     */
    public function autoIncrement(): self
    {
        switch ($this->dbType) {
            case 'mysql':
                $this->modifiers[] = 'AUTO_INCREMENT';
                break;
                
            case 'pgsql':
                // For PostgreSQL, we use SERIAL type instead
                $this->type = 'SERIAL';
                break;
                
            case 'sqlite':
                // For SQLite, we use INTEGER PRIMARY KEY
                $this->type = 'INTEGER';
                $this->primary();
                break;
                
            case 'oracle':
                // For Oracle, we use sequences
                // This is handled in the migration class
                break;
                
            case 'mssql':
                $this->modifiers[] = 'IDENTITY(1,1)';
                break;
                
            case 'sybase':
                $this->modifiers[] = 'IDENTITY';
                break;
        }
        
        return $this;
    }
    
    /**
     * Set the column as the first column in the table (MySQL only).
     *
     * @return $this
     */
    public function first(): self
    {
        if ($this->dbType === 'mysql' && $this->isAlter) {
            $this->modifiers[] = 'FIRST';
        }
        
        return $this;
    }
    
    /**
     * Set the column to be after another column (MySQL only).
     *
     * @param string $column The column name
     * @return $this
     */
    public function after(string $column): self
    {
        if ($this->dbType === 'mysql' && $this->isAlter) {
            $this->modifiers[] = "AFTER {$column}";
        }
        
        return $this;
    }
    
    /**
     * Set the column as a foreign key.
     *
     * @param string $referencedTable The referenced table
     * @param string $referencedColumn The referenced column
     * @param string|null $name The constraint name
     * @param string $onDelete The on delete action
     * @param string $onUpdate The on update action
     * @return $this
     */
    public function references(string $referencedTable, string $referencedColumn = 'id', ?string $name = null, string $onDelete = 'RESTRICT', string $onUpdate = 'RESTRICT'): self
    {
        if ($name === null) {
            $name = $this->table . '_' . $this->name . '_foreign';
        }
        
        $constraint = "CONSTRAINT {$name} FOREIGN KEY ({$this->name}) REFERENCES {$referencedTable} ({$referencedColumn})";
        
        if ($onDelete !== 'RESTRICT') {
            $constraint .= " ON DELETE {$onDelete}";
        }
        
        if ($onUpdate !== 'RESTRICT') {
            $constraint .= " ON UPDATE {$onUpdate}";
        }
        
        $this->modifiers[] = $constraint;
        
        return $this;
    }
    
    /**
     * Set the column to use the current timestamp as default value.
     *
     * @return $this
     */
    public function useCurrent(): self
    {
        switch ($this->dbType) {
            case 'mysql':
                return $this->default('CURRENT_TIMESTAMP');
                
            case 'pgsql':
                return $this->default('CURRENT_TIMESTAMP');
                
            case 'sqlite':
                return $this->default("datetime('now')");
                
            case 'oracle':
                return $this->default('CURRENT_TIMESTAMP');
                
            case 'mssql':
            case 'sybase':
                return $this->default('GETDATE()');
                
            default:
                return $this->default('CURRENT_TIMESTAMP');
        }
    }
    
    /**
     * Set the column to use the current timestamp for both default and on update (MySQL only).
     *
     * @return $this
     */
    public function useCurrentOnUpdate(): self
    {
        if ($this->dbType === 'mysql') {
            $this->modifiers[] = 'DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
        } else {
            $this->useCurrent();
        }
        
        return $this;
    }
    
    /**
     * Set the column collation (MySQL only).
     *
     * @param string $collation The collation
     * @return $this
     */
    public function collation(string $collation): self
    {
        if ($this->dbType === 'mysql') {
            $this->modifiers[] = "COLLATE {$collation}";
        }
        
        return $this;
    }
    
    /**
     * Set the column character set (MySQL only).
     *
     * @param string $charset The character set
     * @return $this
     */
    public function charset(string $charset): self
    {
        if ($this->dbType === 'mysql') {
            $this->modifiers[] = "CHARACTER SET {$charset}";
        }
        
        return $this;
    }
    
    /**
     * Set the column to store JSON (MySQL 5.7+, PostgreSQL 9.2+).
     *
     * @return $this
     */
    public function storedAsJson(): self
    {
        if ($this->dbType === 'mysql') {
            $this->type = 'JSON';
        } elseif ($this->dbType === 'pgsql') {
            $this->type = 'JSONB';
        } else {
            $this->type = 'TEXT';
        }
        
        return $this;
    }
}