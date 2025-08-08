<?php

declare(strict_types=1);

namespace FoxQL\Models;

use FoxQL\Core\Pdo;
use PDOException;
use PDOStatement;

/**
 * Drop Model
 * 
 * This class handles dropping database tables.
 */
class Drop
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
     * Create a new Drop instance.
     *
     * @param \FoxQL\Core\Pdo $pdo
     * @param string $prefix
     */
    public function __construct(Pdo $pdo, string $prefix = '')
    {
        $this->pdo = $pdo;
        $this->prefix = $prefix;
    }

    /**
     * Execute the DROP TABLE query.
     *
     * @param string $table The table name to drop
     * @return PDOStatement The PDO statement
     * @throws PDOException If the query fails
     */
    public function execute(string $table): PDOStatement
    {
        $table = $this->prefix . $table;
        
        // Quote the table name according to the database type
        $table = $this->quoteIdentifier($table);
        
        // Build the DROP TABLE query
        $query = "DROP TABLE IF EXISTS {$table}";
        
        // Execute the query
        return $this->pdo->execute($query);
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
            case 'mariadb':
            case 'sqlite':
                return "`{$identifier}`";
                
            case 'pgsql':
            case 'sybase':
            case 'mssql':
            case 'dblib':
            case 'oracle':
                return "\"{$identifier}\"";
                
            default:
                return $identifier;
        }
    }
}