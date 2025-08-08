<?php

declare(strict_types=1);

namespace FoxQL\Models;

use FoxQL\Core\Pdo;
use PDOException;
use InvalidArgumentException;

/**
 * Connect class for FoxQL
 * 
 * Provides a user-friendly interface for database connections
 */
class Connect
{
    /**
     * The PDO wrapper instance.
     *
     * @var \FoxQL\Core\Pdo
     */
    protected $pdo;

    /**
     * Connection options.
     *
     * @var array
     */
    protected $options;

    /**
     * Whether the connection is established.
     *
     * @var bool
     */
    protected $connected = false;

    /**
     * Create a new Connect instance.
     *
     * @param array $options Connection options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Connect to the database.
     *
     * @param array $options Additional connection options
     * @return self
     * @throws PDOException If the connection fails
     */
    public function connect(array $options = []): self
    {
        // Merge options
        $connectionOptions = array_merge($this->options, $options);

        // Validate required options
        $this->validateOptions($connectionOptions);

        try {
            // Create PDO wrapper instance
            $this->pdo = new Pdo($connectionOptions);
            $this->connected = true;
            return $this;
        } catch (PDOException $e) {
            throw new PDOException("Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Validate connection options.
     *
     * @param array $options Connection options
     * @throws InvalidArgumentException If required options are missing
     */
    protected function validateOptions(array $options): void
    {
        // Check for required options
        if (!isset($options['type'])) {
            throw new InvalidArgumentException('Database type is required.');
        }

        $type = strtolower($options['type']);

        // Normalize database type
        if ($type === 'mariadb') {
            $type = 'mysql';
        }

        // Validate database type
        if (!in_array($type, ['mysql', 'pgsql', 'sqlite', 'sybase', 'oracle', 'mssql'])) {
            throw new InvalidArgumentException("Unsupported database type: {$type}");
        }

        // Check for database name
        if (!isset($options['database'])) {
            throw new InvalidArgumentException('Database name is required.');
        }

        // Check for host (except for SQLite)
        if ($type !== 'sqlite' && !isset($options['host']) && !isset($options['socket'])) {
            throw new InvalidArgumentException('Database host is required for ' . $type . ' connections.');
        }

        // Check for credentials (except for SQLite)
        if ($type !== 'sqlite' && !isset($options['username'])) {
            throw new InvalidArgumentException('Username is required for ' . $type . ' connections.');
        }
    }

    /**
     * Get the PDO wrapper instance.
     *
     * @return \FoxQL\Core\Pdo|null The PDO wrapper instance
     */
    public function getPdo(): ?Pdo
    {
        return $this->pdo;
    }

    /**
     * Check if the connection is established.
     *
     * @return bool Whether the connection is established
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * Get the connection options.
     *
     * @return array The connection options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set connection options.
     *
     * @param array $options Connection options
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * Get the last error message.
     *
     * @return string|null The last error message
     */
    public function getError(): ?string
    {
        return $this->pdo ? $this->pdo->getError() : null;
    }

    /**
     * Get the last error details.
     *
     * @return array|null The last error details
     */
    public function getErrorInfo(): ?array
    {
        return $this->pdo ? $this->pdo->getErrorInfo() : null;
    }

    /**
     * Create a new database connection.
     *
     * @param array $options Connection options
     * @return self
     * @throws PDOException If the connection fails
     */
    public static function create(array $options): self
    {
        return (new self($options))->connect();
    }
}