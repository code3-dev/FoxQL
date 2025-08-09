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
 * Migration Model
 *
 * This class provides Laravel-like migration functionality for FoxQL
 * with support for creating, altering, and dropping tables across
 * all supported database types: MySQL, PostgreSQL, SQLite, Sybase, Oracle, and MSSQL.
 */
class Migration
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
     * The Create model instance.
     *
     * @var \FoxQL\Models\Create
     */
    protected $create;
    
    /**
     * The Drop model instance.
     *
     * @var \FoxQL\Models\Drop
     */
    protected $drop;
    
    /**
     * The migrations table name.
     *
     * @var string
     */
    protected $migrationsTable = 'migrations';
    
    /**
     * Create a new Migration instance.
     *
     * @param \FoxQL\Core\Pdo $pdo The PDO wrapper instance
     * @param string $prefix The table prefix
     */
    public function __construct(Pdo $pdo, string $prefix = '')
    {
        $this->pdo = $pdo;
        $this->prefix = $prefix;
        $this->create = new Create($pdo, $prefix);
        $this->drop = new Drop($pdo, $prefix);
        
        // Initialize migrations table if it doesn't exist
        $this->initMigrationsTable();
    }
    
    /**
     * Initialize the migrations table if it doesn't exist.
     *
     * @return void
     */
    protected function initMigrationsTable(): void
    {
        try {
            // Check if migrations table exists
            $select = new Select($this->pdo, $this->prefix);
            $tableExists = false;
            
            // Different ways to check table existence based on database type
            switch ($this->pdo->getType()) {
                case 'mysql':
                    $query = "SHOW TABLES LIKE '{$this->prefix}{$this->migrationsTable}'";
                    $result = $this->pdo->execute($query);
                    $tableExists = ($result && $result->rowCount() > 0);
                    break;
                    
                case 'pgsql':
                    $query = "SELECT to_regclass('public.{$this->prefix}{$this->migrationsTable}')";
                    $result = $this->pdo->execute($query);
                    $tableExists = ($result && $result->fetchColumn() !== null);
                    break;
                    
                case 'sqlite':
                    $query = "SELECT name FROM sqlite_master WHERE type='table' AND name='{$this->prefix}{$this->migrationsTable}'";
                    $result = $this->pdo->execute($query);
                    $tableExists = ($result && $result->rowCount() > 0);
                    break;
                    
                case 'oracle':
                    $query = "SELECT table_name FROM user_tables WHERE table_name = UPPER('{$this->prefix}{$this->migrationsTable}')";
                    $result = $this->pdo->execute($query);
                    $tableExists = ($result && $result->rowCount() > 0);
                    break;
                    
                case 'mssql':
                    $query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '{$this->prefix}{$this->migrationsTable}'";
                    $result = $this->pdo->execute($query);
                    $tableExists = ($result && $result->rowCount() > 0);
                    break;
                    
                case 'sybase':
                    $query = "SELECT name FROM sysobjects WHERE type = 'U' AND name = '{$this->prefix}{$this->migrationsTable}'";
                    $result = $this->pdo->execute($query);
                    $tableExists = ($result && $result->rowCount() > 0);
                    break;
            }
            
            // Create migrations table if it doesn't exist
            if (!$tableExists) {
                $this->create->execute($this->migrationsTable, [
                    'id' => [
                        $this->getIdColumnType(),
                        'NOT NULL',
                        $this->getAutoIncrementSyntax(),
                        'PRIMARY KEY'
                    ],
                    'migration' => [
                        'VARCHAR(255)',
                        'NOT NULL'
                    ],
                    'batch' => [
                        'INT',
                        'NOT NULL'
                    ],
                    'executed_at' => [
                        $this->getTimestampColumnType(),
                        'NOT NULL'
                    ]
                ]);
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
    }
    
    /**
     * Get the appropriate ID column type based on database type.
     *
     * @return string The ID column type
     */
    protected function getIdColumnType(): string
    {
        switch ($this->pdo->getType()) {
            case 'mysql':
            case 'mssql':
            case 'sybase':
                return 'INT';
                
            case 'pgsql':
                return 'SERIAL';
                
            case 'sqlite':
                return 'INTEGER';
                
            case 'oracle':
                return 'NUMBER(10)';
                
            default:
                return 'INT';
        }
    }
    
    /**
     * Get the appropriate auto-increment syntax based on database type.
     *
     * @return string The auto-increment syntax
     */
    protected function getAutoIncrementSyntax(): string
    {
        switch ($this->pdo->getType()) {
            case 'mysql':
                return 'AUTO_INCREMENT';
                
            case 'mssql':
                return 'IDENTITY(1,1)';
                
            case 'sqlite':
            case 'pgsql': // For PostgreSQL, SERIAL type already includes auto-increment
                return '';
                
            case 'oracle':
                return ''; // Oracle uses sequences, handled separately
                
            case 'sybase':
                return 'IDENTITY';
                
            default:
                return 'AUTO_INCREMENT';
        }
    }
    
    /**
     * Get the appropriate timestamp column type based on database type.
     *
     * @return string The timestamp column type
     */
    protected function getTimestampColumnType(): string
    {
        switch ($this->pdo->getType()) {
            case 'mysql':
                return 'TIMESTAMP';
                
            case 'pgsql':
                return 'TIMESTAMP';
                
            case 'sqlite':
                return 'TEXT';
                
            case 'oracle':
                return 'TIMESTAMP';
                
            case 'mssql':
                return 'DATETIME';
                
            case 'sybase':
                return 'DATETIME';
                
            default:
                return 'TIMESTAMP';
        }
    }
    
    /**
     * Get the current timestamp in the appropriate format for the database.
     *
     * @return string The current timestamp
     */
    protected function getCurrentTimestamp(): string
    {
        switch ($this->pdo->getType()) {
            case 'mysql':
                return 'NOW()';
                
            case 'pgsql':
                return 'CURRENT_TIMESTAMP';
                
            case 'sqlite':
                return "datetime('now')";
                
            case 'oracle':
                return 'CURRENT_TIMESTAMP';
                
            case 'mssql':
                return 'GETDATE()';
                
            case 'sybase':
                return 'GETDATE()';
                
            default:
                return 'CURRENT_TIMESTAMP';
        }
    }
    
    /**
     * Create a new table.
     *
     * @param string $table The table name
     * @param callable $callback The table definition callback
     * @return bool True on success, false on failure
     */
    public function createTable(string $table, callable $callback): bool
    {
        try {
            $blueprint = new Blueprint($table, $this->pdo->getType());
            $callback($blueprint);
            
            $columns = $blueprint->getColumns();
            $options = $blueprint->getOptions();
            
            $this->create->execute($table, $columns, $options);
            return true;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Drop a table if it exists.
     *
     * @param string $table The table name
     * @return bool True on success, false on failure
     */
    public function dropTable(string $table): bool
    {
        try {
            $this->drop->execute($table);
            return true;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Rename a table.
     *
     * @param string $from The current table name
     * @param string $to The new table name
     * @return bool True on success, false on failure
     */
    public function renameTable(string $from, string $to): bool
    {
        try {
            $query = '';
            
            switch ($this->pdo->getType()) {
                case 'mysql':
                case 'pgsql':
                    $query = "ALTER TABLE {$this->prefix}{$from} RENAME TO {$this->prefix}{$to}";
                    break;
                    
                case 'sqlite':
                    $query = "ALTER TABLE {$this->prefix}{$from} RENAME TO {$this->prefix}{$to}";
                    break;
                    
                case 'oracle':
                    $query = "ALTER TABLE {$this->prefix}{$from} RENAME TO {$this->prefix}{$to}";
                    break;
                    
                case 'mssql':
                    $query = "EXEC sp_rename '{$this->prefix}{$from}', '{$this->prefix}{$to}'";
                    break;
                    
                case 'sybase':
                    $query = "EXEC sp_rename '{$this->prefix}{$from}', '{$this->prefix}{$to}'";
                    break;
            }
            
            $this->pdo->execute($query);
            return true;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Alter a table structure.
     *
     * @param string $table The table name
     * @param callable $callback The table modification callback
     * @return bool True on success, false on failure
     */
    public function alterTable(string $table, callable $callback): bool
    {
        try {
            $blueprint = new Blueprint($table, $this->pdo->getType(), true);
            $callback($blueprint);
            
            $commands = $blueprint->getAlterCommands();
            
            // Execute each alter command
            foreach ($commands as $command) {
                $this->pdo->execute($command);
            }
            
            return true;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Run migrations from a directory.
     *
     * @param string $directory The directory containing migration files
     * @return array Array of executed migrations
     */
    public function migrate(string $directory): array
    {
        $executed = [];
        
        try {
            // Get already executed migrations
            $query = "SELECT migration FROM {$this->prefix}{$this->migrationsTable}";
            $result = $this->pdo->execute($query);
            $executedMigrations = $result ? $result->fetchAll(NativePDO::FETCH_COLUMN) : [];
            
            // Get current batch number
            $query = "SELECT MAX(batch) FROM {$this->prefix}{$this->migrationsTable}";
            $result = $this->pdo->execute($query);
            $batch = $result ? (int)($result->fetchColumn() ?: 0) + 1 : 1;
            
            // Get all migration files
            $files = glob($directory . '/*.php');
            sort($files); // Sort by filename
            
            // Begin transaction
            $this->pdo->getPdo()->beginTransaction();
            
            foreach ($files as $file) {
                $migrationName = basename($file, '.php');
                
                // Skip already executed migrations
                if (in_array($migrationName, $executedMigrations)) {
                    continue;
                }
                
                // Include the migration file
                require_once $file;
                
                // Extract class name from filename (assuming PSR-4 naming)
                $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $migrationName)));
                $className = "\\Migrations\\{$className}";
                
                if (class_exists($className)) {
                    $migration = new $className();
                    
                    // Run the migration
                    if (method_exists($migration, 'up')) {
                        $migration->up($this);
                        
                        // Record the migration
                        $timestamp = new Raw($this->getCurrentTimestamp());
                        $query = "INSERT INTO {$this->prefix}{$this->migrationsTable} (migration, batch, executed_at) VALUES (?, ?, {$timestamp->getValue()})";
                        $this->pdo->execute($query, [$migrationName, $batch]);
                        
                        $executed[] = $migrationName;
                    }
                }
            }
            
            // Commit transaction
            $this->pdo->getPdo()->commit();
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            if ($this->pdo->getPdo()->inTransaction()) {
                $this->pdo->getPdo()->rollBack();
            }
            
            $this->error = $e->getMessage();
        }
        
        return $executed;
    }
    
    /**
     * Rollback the last batch of migrations.
     *
     * @param string $directory The directory containing migration files
     * @param int $steps Number of batches to rollback (default: 1)
     * @return array Array of rolled back migrations
     */
    public function rollback(string $directory, int $steps = 1): array
    {
        $rolledBack = [];
        
        try {
            // Get the last batch(es) to rollback
            $query = "SELECT DISTINCT batch FROM {$this->prefix}{$this->migrationsTable} ORDER BY batch DESC LIMIT {$steps}";
            $result = $this->pdo->execute($query);
            $batches = $result ? $result->fetchAll(NativePDO::FETCH_COLUMN) : [];
            
            if (empty($batches)) {
                return $rolledBack;
            }
            
            $batchList = implode(',', $batches);
            
            // Get migrations from these batches
            $query = "SELECT migration FROM {$this->prefix}{$this->migrationsTable} WHERE batch IN ({$batchList}) ORDER BY id DESC";
            $result = $this->pdo->execute($query);
            $migrations = $result ? $result->fetchAll(NativePDO::FETCH_COLUMN) : [];
            
            // Begin transaction
            $this->pdo->getPdo()->beginTransaction();
            
            foreach ($migrations as $migrationName) {
                $file = $directory . '/' . $migrationName . '.php';
                
                if (file_exists($file)) {
                    // Include the migration file
                    require_once $file;
                    
                    // Extract class name from filename
                    $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $migrationName)));
                    $className = "\\Migrations\\{$className}";
                    
                    if (class_exists($className)) {
                        $migration = new $className();
                        
                        // Run the down method
                        if (method_exists($migration, 'down')) {
                            $migration->down($this);
                            
                            // Remove the migration record
                            $query = "DELETE FROM {$this->prefix}{$this->migrationsTable} WHERE migration = ?";
                            $this->pdo->execute($query, [$migrationName]);
                            
                            $rolledBack[] = $migrationName;
                        }
                    }
                }
            }
            
            // Commit transaction
            $this->pdo->getPdo()->commit();
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            if ($this->pdo->getPdo()->inTransaction()) {
                $this->pdo->getPdo()->rollBack();
            }
            
            $this->error = $e->getMessage();
        }
        
        return $rolledBack;
    }
    
    /**
     * Reset all migrations (rollback everything).
     *
     * @param string $directory The directory containing migration files
     * @return array Array of rolled back migrations
     */
    public function reset(string $directory): array
    {
        $rolledBack = [];
        
        try {
            // Get all migrations in reverse order
            $query = "SELECT migration FROM {$this->prefix}{$this->migrationsTable} ORDER BY id DESC";
            $result = $this->pdo->execute($query);
            $migrations = $result ? $result->fetchAll(NativePDO::FETCH_COLUMN) : [];
            
            // Begin transaction
            $this->pdo->getPdo()->beginTransaction();
            
            foreach ($migrations as $migrationName) {
                $file = $directory . '/' . $migrationName . '.php';
                
                if (file_exists($file)) {
                    // Include the migration file
                    require_once $file;
                    
                    // Extract class name from filename
                    $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $migrationName)));
                    $className = "\\Migrations\\{$className}";
                    
                    if (class_exists($className)) {
                        $migration = new $className();
                        
                        // Run the down method
                        if (method_exists($migration, 'down')) {
                            $migration->down($this);
                            $rolledBack[] = $migrationName;
                        }
                    }
                }
            }
            
            // Clear the migrations table
            $query = "DELETE FROM {$this->prefix}{$this->migrationsTable}";
            $this->pdo->execute($query);
            
            // Commit transaction
            $this->pdo->getPdo()->commit();
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            if ($this->pdo->getPdo()->inTransaction()) {
                $this->pdo->getPdo()->rollBack();
            }
            
            $this->error = $e->getMessage();
        }
        
        return $rolledBack;
    }
    
    /**
     * Refresh all migrations (reset and re-run).
     *
     * @param string $directory The directory containing migration files
     * @return array Array with rolled back and executed migrations
     */
    public function refresh(string $directory): array
    {
        $result = [
            'rolledBack' => [],
            'executed' => []
        ];
        
        // Reset all migrations
        $result['rolledBack'] = $this->reset($directory);
        
        // Run all migrations again
        $result['executed'] = $this->migrate($directory);
        
        return $result;
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