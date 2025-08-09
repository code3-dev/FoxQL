<?php

declare(strict_types=1);

namespace FoxQL\Models;

use InvalidArgumentException;

/**
 * Blueprint Model
 *
 * This class provides a fluent interface for defining database table structure
 * with support for all database types: MySQL, PostgreSQL, SQLite, Sybase, Oracle, and MSSQL.
 */
class Blueprint
{
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
     * The columns definition.
     *
     * @var array
     */
    protected $columns = [];
    
    /**
     * The table options.
     *
     * @var array
     */
    protected $options = [];
    
    /**
     * The alter commands.
     *
     * @var array
     */
    protected $alterCommands = [];
    
    /**
     * Whether this blueprint is for an alter operation.
     *
     * @var bool
     */
    protected $isAlter;
    
    /**
     * Create a new Blueprint instance.
     *
     * @param string $table The table name
     * @param string $dbType The database type
     * @param bool $isAlter Whether this blueprint is for an alter operation
     */
    public function __construct(string $table, string $dbType, bool $isAlter = false)
    {
        $this->table = $table;
        $this->dbType = strtolower($dbType);
        $this->isAlter = $isAlter;
    }
    
    /**
     * Get the columns definition.
     *
     * @return array The columns definition
     */
    public function getColumns(): array
    {
        return $this->columns;
    }
    
    /**
     * Get the table options.
     *
     * @return array The table options
     */
    public function getOptions(): array
    {
        return $this->options;
    }
    
    /**
     * Get the alter commands.
     *
     * @return array The alter commands
     */
    public function getAlterCommands(): array
    {
        return $this->alterCommands;
    }
    
    /**
     * Add a column of type CHAR.
     *
     * @param string $column The column name
     * @param int $length The column length
     * @return \FoxQL\Models\Column The column instance
     */
    public function char(string $column, int $length = 255): Column
    {
        return $this->addColumn($column, "CHAR({$length})");
    }
    
    /**
     * Add a column of type VARCHAR.
     *
     * @param string $column The column name
     * @param int $length The column length
     * @return \FoxQL\Models\Column The column instance
     */
    public function string(string $column, int $length = 255): Column
    {
        return $this->addColumn($column, "VARCHAR({$length})");
    }
    
    /**
     * Add a column of type TEXT.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function text(string $column): Column
    {
        return $this->addColumn($column, "TEXT");
    }
    
    /**
     * Add a column of type MEDIUMTEXT (or equivalent).
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function mediumText(string $column): Column
    {
        $type = $this->dbType === 'mysql' ? 'MEDIUMTEXT' : 'TEXT';
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type LONGTEXT (or equivalent).
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function longText(string $column): Column
    {
        $type = $this->dbType === 'mysql' ? 'LONGTEXT' : 'TEXT';
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type INTEGER.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function integer(string $column): Column
    {
        $type = 'INTEGER';
        
        if ($this->dbType === 'oracle') {
            $type = 'NUMBER(10)';
        }
        
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type BIGINT.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function bigInteger(string $column): Column
    {
        $type = 'BIGINT';
        
        if ($this->dbType === 'oracle') {
            $type = 'NUMBER(19)';
        }
        
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type TINYINT.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function tinyInteger(string $column): Column
    {
        $type = 'TINYINT';
        
        if ($this->dbType === 'pgsql') {
            $type = 'SMALLINT';
        } elseif ($this->dbType === 'oracle') {
            $type = 'NUMBER(3)';
        } elseif ($this->dbType === 'sqlite') {
            $type = 'INTEGER';
        }
        
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type SMALLINT.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function smallInteger(string $column): Column
    {
        $type = 'SMALLINT';
        
        if ($this->dbType === 'oracle') {
            $type = 'NUMBER(5)';
        }
        
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type MEDIUMINT.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function mediumInteger(string $column): Column
    {
        $type = 'MEDIUMINT';
        
        if ($this->dbType === 'pgsql' || $this->dbType === 'oracle' || $this->dbType === 'mssql' || $this->dbType === 'sybase') {
            $type = 'INTEGER';
        } elseif ($this->dbType === 'sqlite') {
            $type = 'INTEGER';
        }
        
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add an auto-incrementing column.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function increments(string $column): Column
    {
        $column = $this->getIncrementingColumn($column);
        $column->primary();
        return $column;
    }
    
    /**
     * Add an auto-incrementing big integer column.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function bigIncrements(string $column): Column
    {
        $column = $this->getIncrementingBigColumn($column);
        $column->primary();
        return $column;
    }
    
    /**
     * Get an auto-incrementing column instance.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    protected function getIncrementingColumn(string $column): Column
    {
        switch ($this->dbType) {
            case 'mysql':
                return $this->addColumn($column, 'INT', ['AUTO_INCREMENT']);
                
            case 'pgsql':
                return $this->addColumn($column, 'SERIAL');
                
            case 'sqlite':
                return $this->addColumn($column, 'INTEGER');
                
            case 'oracle':
                // Oracle uses sequences, handled separately
                return $this->addColumn($column, 'NUMBER(10)');
                
            case 'mssql':
                return $this->addColumn($column, 'INT', ['IDENTITY(1,1)']);
                
            case 'sybase':
                return $this->addColumn($column, 'INT', ['IDENTITY']);
                
            default:
                return $this->addColumn($column, 'INT', ['AUTO_INCREMENT']);
        }
    }
    
    /**
     * Get an auto-incrementing big integer column instance.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    protected function getIncrementingBigColumn(string $column): Column
    {
        switch ($this->dbType) {
            case 'mysql':
                return $this->addColumn($column, 'BIGINT', ['AUTO_INCREMENT']);
                
            case 'pgsql':
                return $this->addColumn($column, 'BIGSERIAL');
                
            case 'sqlite':
                return $this->addColumn($column, 'INTEGER');
                
            case 'oracle':
                // Oracle uses sequences, handled separately
                return $this->addColumn($column, 'NUMBER(19)');
                
            case 'mssql':
                return $this->addColumn($column, 'BIGINT', ['IDENTITY(1,1)']);
                
            case 'sybase':
                return $this->addColumn($column, 'BIGINT', ['IDENTITY']);
                
            default:
                return $this->addColumn($column, 'BIGINT', ['AUTO_INCREMENT']);
        }
    }
    
    /**
     * Add a column of type DECIMAL.
     *
     * @param string $column The column name
     * @param int $precision The precision
     * @param int $scale The scale
     * @return \FoxQL\Models\Column The column instance
     */
    public function decimal(string $column, int $precision = 8, int $scale = 2): Column
    {
        return $this->addColumn($column, "DECIMAL({$precision},{$scale})");
    }
    
    /**
     * Add a column of type FLOAT.
     *
     * @param string $column The column name
     * @param int $precision The precision
     * @param int $scale The scale
     * @return \FoxQL\Models\Column The column instance
     */
    public function float(string $column, int $precision = 8, int $scale = 2): Column
    {
        $type = "FLOAT";
        
        if ($this->dbType === 'oracle') {
            $type = "FLOAT({$precision})"; 
        } elseif ($precision !== 8 || $scale !== 2) {
            $type = "FLOAT({$precision},{$scale})";
        }
        
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type DOUBLE.
     *
     * @param string $column The column name
     * @param int $precision The precision
     * @param int $scale The scale
     * @return \FoxQL\Models\Column The column instance
     */
    public function double(string $column, int $precision = 8, int $scale = 2): Column
    {
        $type = "DOUBLE";
        
        if ($this->dbType === 'pgsql') {
            $type = "DOUBLE PRECISION";
        } elseif ($this->dbType === 'oracle') {
            $type = "FLOAT({$precision})";
        } elseif ($this->dbType !== 'pgsql' && ($precision !== 8 || $scale !== 2)) {
            $type = "DOUBLE({$precision},{$scale})";
        }
        
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type BOOLEAN.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function boolean(string $column): Column
    {
        $type = 'BOOLEAN';
        
        if ($this->dbType === 'mysql') {
            $type = 'TINYINT(1)';
        } elseif ($this->dbType === 'mssql' || $this->dbType === 'sybase') {
            $type = 'BIT';
        } elseif ($this->dbType === 'oracle') {
            $type = 'NUMBER(1)';
        }
        
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type ENUM.
     *
     * @param string $column The column name
     * @param array $values The enum values
     * @return \FoxQL\Models\Column The column instance
     */
    public function enum(string $column, array $values): Column
    {
        $valuesStr = "'" . implode("', '", $values) . "'";
        
        if ($this->dbType === 'mysql') {
            return $this->addColumn($column, "ENUM({$valuesStr})");
        } elseif ($this->dbType === 'pgsql') {
            // For PostgreSQL, we need to create a custom type first
            // This is handled in the migration class
            return $this->addColumn($column, "{$this->table}_{$column}_enum");
        } else {
            // For other databases, use VARCHAR with CHECK constraint
            $column = $this->addColumn($column, "VARCHAR(255)");
            $column->check("$column IN ({$valuesStr})");
            return $column;
        }
    }
    
    /**
     * Add a column of type JSON.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function json(string $column): Column
    {
        $type = 'JSON';
        
        if ($this->dbType === 'mysql' && version_compare(PHP_VERSION, '5.7.0', '<')) {
            $type = 'TEXT';
        } elseif ($this->dbType === 'sqlite' || $this->dbType === 'oracle' || $this->dbType === 'mssql' || $this->dbType === 'sybase') {
            $type = 'TEXT';
        }
        
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type DATE.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function date(string $column): Column
    {
        return $this->addColumn($column, 'DATE');
    }
    
    /**
     * Add a column of type DATETIME.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function dateTime(string $column): Column
    {
        $type = 'DATETIME';
        
        if ($this->dbType === 'pgsql') {
            $type = 'TIMESTAMP';
        } elseif ($this->dbType === 'oracle') {
            $type = 'TIMESTAMP';
        }
        
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type TIMESTAMP.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function timestamp(string $column): Column
    {
        $type = 'TIMESTAMP';
        
        if ($this->dbType === 'sqlite') {
            $type = 'DATETIME';
        } elseif ($this->dbType === 'mssql' || $this->dbType === 'sybase') {
            $type = 'DATETIME';
        }
        
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type TIME.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function time(string $column): Column
    {
        return $this->addColumn($column, 'TIME');
    }
    
    /**
     * Add a column of type BINARY.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function binary(string $column): Column
    {
        $type = 'BLOB';
        
        if ($this->dbType === 'pgsql') {
            $type = 'BYTEA';
        } elseif ($this->dbType === 'mssql' || $this->dbType === 'sybase') {
            $type = 'VARBINARY(MAX)';
        } elseif ($this->dbType === 'oracle') {
            $type = 'BLOB';
        }
        
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type UUID.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function uuid(string $column): Column
    {
        $type = 'VARCHAR(36)';
        
        if ($this->dbType === 'pgsql') {
            $type = 'UUID';
        }
        
        return $this->addColumn($column, $type);
    }
    
    /**
     * Add a column of type IP address.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function ipAddress(string $column): Column
    {
        return $this->addColumn($column, 'VARCHAR(45)');
    }
    
    /**
     * Add a column of type MAC address.
     *
     * @param string $column The column name
     * @return \FoxQL\Models\Column The column instance
     */
    public function macAddress(string $column): Column
    {
        return $this->addColumn($column, 'VARCHAR(17)');
    }
    
    /**
     * Add TIMESTAMP columns for created_at and updated_at.
     *
     * @return void
     */
    public function timestamps(): void
    {
        $this->timestamp('created_at')->nullable();
        $this->timestamp('updated_at')->nullable();
    }
    
    /**
     * Add a TIMESTAMP column for deleted_at (soft deletes).
     *
     * @return \FoxQL\Models\Column The column instance
     */
    public function softDeletes(): Column
    {
        return $this->timestamp('deleted_at')->nullable();
    }
    
    /**
     * Add a primary key constraint.
     *
     * @param string|array $columns The column(s) for the primary key
     * @return void
     */
    public function primary($columns): void
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $columnsStr = implode(', ', $columns);
        
        if ($this->isAlter) {
            $this->alterCommands[] = "ALTER TABLE {$this->table} ADD PRIMARY KEY ({$columnsStr})";
        } else {
            $this->options[] = "PRIMARY KEY ({$columnsStr})";
        }
    }
    
    /**
     * Add a unique constraint.
     *
     * @param string|array $columns The column(s) for the unique constraint
     * @param string|null $name The constraint name
     * @return void
     */
    public function unique($columns, ?string $name = null): void
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $columnsStr = implode(', ', $columns);
        
        if ($name === null) {
            $name = $this->table . '_' . implode('_', $columns) . '_unique';
        }
        
        if ($this->isAlter) {
            $this->alterCommands[] = "ALTER TABLE {$this->table} ADD CONSTRAINT {$name} UNIQUE ({$columnsStr})";
        } else {
            $this->options[] = "CONSTRAINT {$name} UNIQUE ({$columnsStr})";
        }
    }
    
    /**
     * Add an index.
     *
     * @param string|array $columns The column(s) for the index
     * @param string|null $name The index name
     * @return void
     */
    public function index($columns, ?string $name = null): void
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $columnsStr = implode(', ', $columns);
        
        if ($name === null) {
            $name = $this->table . '_' . implode('_', $columns) . '_index';
        }
        
        if ($this->isAlter) {
            $this->alterCommands[] = "CREATE INDEX {$name} ON {$this->table} ({$columnsStr})";
        } else {
            // For non-alter operations, we'll create the index after table creation
            $this->options[] = "-- CREATE INDEX {$name} ON {$this->table} ({$columnsStr})";
        }
    }
    
    /**
     * Add a foreign key constraint.
     *
     * @param string|array $columns The column(s) for the foreign key
     * @param string $referencedTable The referenced table
     * @param string|array $referencedColumns The referenced column(s)
     * @param string|null $name The constraint name
     * @param string $onDelete The on delete action
     * @param string $onUpdate The on update action
     * @return void
     */
    public function foreign($columns, string $referencedTable, $referencedColumns, ?string $name = null, string $onDelete = 'RESTRICT', string $onUpdate = 'RESTRICT'): void
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $referencedColumns = is_array($referencedColumns) ? $referencedColumns : [$referencedColumns];
        
        $columnsStr = implode(', ', $columns);
        $referencedColumnsStr = implode(', ', $referencedColumns);
        
        if ($name === null) {
            $name = $this->table . '_' . implode('_', $columns) . '_foreign';
        }
        
        $constraint = "CONSTRAINT {$name} FOREIGN KEY ({$columnsStr}) REFERENCES {$referencedTable} ({$referencedColumnsStr})";
        
        if ($onDelete !== 'RESTRICT') {
            $constraint .= " ON DELETE {$onDelete}";
        }
        
        if ($onUpdate !== 'RESTRICT') {
            $constraint .= " ON UPDATE {$onUpdate}";
        }
        
        if ($this->isAlter) {
            $this->alterCommands[] = "ALTER TABLE {$this->table} ADD {$constraint}";
        } else {
            $this->options[] = $constraint;
        }
    }
    
    /**
     * Add a column to the blueprint.
     *
     * @param string $name The column name
     * @param string $type The column type
     * @param array $modifiers The column modifiers
     * @return \FoxQL\Models\Column The column instance
     */
    protected function addColumn(string $name, string $type, array $modifiers = []): Column
    {
        $column = new Column($name, $type, $modifiers, $this->table, $this->dbType, $this->isAlter);
        
        if ($this->isAlter) {
            // For alter operations, we'll add the column definition to the alter commands
            $this->alterCommands[] = $column->getAlterCommand();
        } else {
            // For create operations, we'll add the column definition to the columns array
            $this->columns[$name] = $column->getDefinition();
        }
        
        return $column;
    }
    
    /**
     * Add a check constraint.
     *
     * @param string $constraint The check constraint
     * @param string|null $name The constraint name
     * @return void
     */
    public function check(string $constraint, ?string $name = null): void
    {
        if ($name === null) {
            $name = $this->table . '_check_' . count($this->options);
        }
        
        if ($this->isAlter) {
            $this->alterCommands[] = "ALTER TABLE {$this->table} ADD CONSTRAINT {$name} CHECK ({$constraint})";
        } else {
            $this->options[] = "CONSTRAINT {$name} CHECK ({$constraint})";
        }
    }
    
    /**
     * Set the table engine (MySQL only).
     *
     * @param string $engine The table engine
     * @return void
     */
    public function engine(string $engine): void
    {
        if ($this->dbType === 'mysql') {
            $this->options[] = "ENGINE={$engine}";
        }
    }
    
    /**
     * Set the table character set (MySQL only).
     *
     * @param string $charset The character set
     * @return void
     */
    public function charset(string $charset): void
    {
        if ($this->dbType === 'mysql') {
            $this->options[] = "DEFAULT CHARACTER SET {$charset}";
        }
    }
    
    /**
     * Set the table collation (MySQL only).
     *
     * @param string $collation The collation
     * @return void
     */
    public function collation(string $collation): void
    {
        if ($this->dbType === 'mysql') {
            $this->options[] = "COLLATE {$collation}";
        }
    }
    
    /**
     * Add a comment to the table (MySQL, PostgreSQL).
     *
     * @param string $comment The table comment
     * @return void
     */
    public function comment(string $comment): void
    {
        if ($this->dbType === 'mysql') {
            $this->options[] = "COMMENT='{$comment}'";
        } elseif ($this->dbType === 'pgsql' && $this->isAlter) {
            $this->alterCommands[] = "COMMENT ON TABLE {$this->table} IS '{$comment}'";
        }
    }
    
    /**
     * Drop a column.
     *
     * @param string $column The column name
     * @return void
     */
    public function dropColumn(string $column): void
    {
        if (!$this->isAlter) {
            throw new InvalidArgumentException('Cannot drop column in create operation');
        }
        
        $this->alterCommands[] = "ALTER TABLE {$this->table} DROP COLUMN {$column}";
    }
    
    /**
     * Drop multiple columns.
     *
     * @param array $columns The column names
     * @return void
     */
    public function dropColumns(array $columns): void
    {
        foreach ($columns as $column) {
            $this->dropColumn($column);
        }
    }
    
    /**
     * Drop a primary key.
     *
     * @param string|null $name The constraint name (for some databases)
     * @return void
     */
    public function dropPrimary(?string $name = null): void
    {
        if (!$this->isAlter) {
            throw new InvalidArgumentException('Cannot drop primary key in create operation');
        }
        
        if ($this->dbType === 'mysql') {
            $this->alterCommands[] = "ALTER TABLE {$this->table} DROP PRIMARY KEY";
        } elseif ($name !== null) {
            $this->alterCommands[] = "ALTER TABLE {$this->table} DROP CONSTRAINT {$name}";
        } else {
            $this->alterCommands[] = "ALTER TABLE {$this->table} DROP CONSTRAINT {$this->table}_pkey";
        }
    }
    
    /**
     * Drop a unique constraint.
     *
     * @param string $name The constraint name
     * @return void
     */
    public function dropUnique(string $name): void
    {
        if (!$this->isAlter) {
            throw new InvalidArgumentException('Cannot drop unique constraint in create operation');
        }
        
        if ($this->dbType === 'mysql') {
            $this->alterCommands[] = "ALTER TABLE {$this->table} DROP INDEX {$name}";
        } else {
            $this->alterCommands[] = "ALTER TABLE {$this->table} DROP CONSTRAINT {$name}";
        }
    }
    
    /**
     * Drop an index.
     *
     * @param string $name The index name
     * @return void
     */
    public function dropIndex(string $name): void
    {
        if (!$this->isAlter) {
            throw new InvalidArgumentException('Cannot drop index in create operation');
        }
        
        if ($this->dbType === 'mysql' || $this->dbType === 'mssql') {
            $this->alterCommands[] = "DROP INDEX {$name} ON {$this->table}";
        } else {
            $this->alterCommands[] = "DROP INDEX {$name}";
        }
    }
    
    /**
     * Drop a foreign key constraint.
     *
     * @param string $name The constraint name
     * @return void
     */
    public function dropForeign(string $name): void
    {
        if (!$this->isAlter) {
            throw new InvalidArgumentException('Cannot drop foreign key in create operation');
        }
        
        if ($this->dbType === 'mysql') {
            $this->alterCommands[] = "ALTER TABLE {$this->table} DROP FOREIGN KEY {$name}";
        } else {
            $this->alterCommands[] = "ALTER TABLE {$this->table} DROP CONSTRAINT {$name}";
        }
    }
    
    /**
     * Rename a column.
     *
     * @param string $from The current column name
     * @param string $to The new column name
     * @return void
     */
    public function renameColumn(string $from, string $to): void
    {
        if (!$this->isAlter) {
            throw new InvalidArgumentException('Cannot rename column in create operation');
        }
        
        switch ($this->dbType) {
            case 'mysql':
                // MySQL requires the column definition for renaming
                // This is a placeholder, as the actual implementation would need to fetch the column definition
                $this->alterCommands[] = "ALTER TABLE {$this->table} CHANGE {$from} {$to} /* COLUMN DEFINITION */";
                break;
                
            case 'pgsql':
            case 'sqlite':
                $this->alterCommands[] = "ALTER TABLE {$this->table} RENAME COLUMN {$from} TO {$to}";
                break;
                
            case 'oracle':
                $this->alterCommands[] = "ALTER TABLE {$this->table} RENAME COLUMN {$from} TO {$to}";
                break;
                
            case 'mssql':
            case 'sybase':
                $this->alterCommands[] = "EXEC sp_rename '{$this->table}.{$from}', '{$to}', 'COLUMN'";
                break;
        }
    }
}