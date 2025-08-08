<?php

declare(strict_types=1);

namespace FoxQL\Core;

use PDO as NativePDO;
use PDOException;
use PDOStatement;
use InvalidArgumentException;

/**
 * Core PDO wrapper class for FoxQL
 * 
 * Provides a secure and standardized interface for database connections
 */
class Pdo
{
    /**
     * The PDO database connection instance.
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * The database type.
     *
     * @var string
     */
    protected $type;

    /**
     * The DSN connection string.
     *
     * @var string
     */
    protected $dsn;

    /**
     * The table prefix.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Current PDO statement instance.
     *
     * @var \PDOStatement
     */
    protected $statement;

    /**
     * The last error message.
     *
     * @var string|null
     */
    protected $error = null;

    /**
     * The last error details.
     *
     * @var array|null
     */
    protected $errorInfo = null;

    /**
     * Establish a database connection.
     *
     * @param array $options Connection options
     * @throws PDOException If the connection fails
     */
    public function __construct(array $options)
    {
        if (isset($options['prefix'])) {
            $this->prefix = $options['prefix'];
        }

        if (!isset($options['type'])) {
            throw new InvalidArgumentException('Database type is required.');
        }

        $this->type = strtolower($options['type']);

        // Normalize database type
        if ($this->type === 'mariadb') {
            $this->type = 'mysql';
        }

        // Validate supported database types
        if (!in_array($this->type, ['mysql', 'pgsql', 'sqlite', 'sybase', 'oracle', 'mssql'])) {
            throw new InvalidArgumentException("Unsupported database type: {$this->type}");
        }

        // If a PDO instance is provided, use it
        if (isset($options['pdo'])) {
            if (!$options['pdo'] instanceof NativePDO) {
                throw new InvalidArgumentException('Invalid PDO object supplied.');
            }

            $this->pdo = $options['pdo'];
            return;
        }

        // Otherwise, create a new PDO connection
        $this->connect($options);
    }

    /**
     * Create a new PDO connection.
     *
     * @param array $options Connection options
     * @throws PDOException If the connection fails
     */
    protected function connect(array $options): void
    {
        $dsn = $this->buildDsn($options);
        $this->dsn = $dsn;

        try {
            $this->pdo = new NativePDO(
                $dsn,
                $options['username'] ?? null,
                $options['password'] ?? null,
                $options['options'] ?? []
            );

            // Set error mode
            if (isset($options['error'])) {
                $this->pdo->setAttribute(
                    NativePDO::ATTR_ERRMODE,
                    in_array($options['error'], [
                        NativePDO::ERRMODE_SILENT,
                        NativePDO::ERRMODE_WARNING,
                        NativePDO::ERRMODE_EXCEPTION
                    ]) ?
                    $options['error'] :
                    NativePDO::ERRMODE_SILENT
                );
            } else {
                // Default to exception mode for better error handling
                $this->pdo->setAttribute(NativePDO::ATTR_ERRMODE, NativePDO::ERRMODE_EXCEPTION);
            }

            // Execute initialization commands
            $this->executeInitCommands($options);

        } catch (PDOException $e) {
            throw new PDOException("Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Build the DSN string for the database connection.
     *
     * @param array $options Connection options
     * @return string The DSN string
     * @throws InvalidArgumentException If the connection options are incorrect
     */
    protected function buildDsn(array $options): string
    {
        $attr = [];
        $isPort = isset($options['port']) && is_numeric($options['port']);
        $port = $isPort ? $options['port'] : null;

        switch ($this->type) {
            case 'mysql':
                $attr = [
                    'driver' => 'mysql',
                    'dbname' => $options['database']
                ];

                if (isset($options['socket'])) {
                    $attr['unix_socket'] = $options['socket'];
                } else {
                    $attr['host'] = $options['host'] ?? 'localhost';

                    if ($isPort) {
                        $attr['port'] = $port;
                    }
                }
                break;

            case 'pgsql':
                $attr = [
                    'driver' => 'pgsql',
                    'host' => $options['host'] ?? 'localhost',
                    'dbname' => $options['database']
                ];

                if ($isPort) {
                    $attr['port'] = $port;
                }
                break;

            case 'sqlite':
                $attr = [
                    'driver' => 'sqlite',
                    $options['database']
                ];
                break;

            case 'sybase':
                $attr = [
                    'driver' => 'dblib',
                    'host' => $options['host'] ?? 'localhost',
                    'dbname' => $options['database']
                ];

                if ($isPort) {
                    $attr['port'] = $port;
                }
                break;

            case 'oracle':
                $attr = [
                    'driver' => 'oci',
                    'dbname' => isset($options['host']) ?
                        '//' . $options['host'] . ($isPort ? ':' . $port : ':1521') . '/' . $options['database'] :
                        $options['database']
                ];

                if (isset($options['charset'])) {
                    $attr['charset'] = $options['charset'];
                }
                break;

            case 'mssql':
                if (isset($options['driver']) && $options['driver'] === 'dblib') {
                    $attr = [
                        'driver' => 'dblib',
                        'host' => $options['host'] . ($isPort ? ':' . $port : ''),
                        'dbname' => $options['database']
                    ];

                    if (isset($options['appname'])) {
                        $attr['appname'] = $options['appname'];
                    }

                    if (isset($options['charset'])) {
                        $attr['charset'] = $options['charset'];
                    }
                } else {
                    $attr = [
                        'driver' => 'sqlsrv',
                        'Server' => $options['host'] . ($isPort ? ',' . $port : ''),
                        'Database' => $options['database']
                    ];

                    if (isset($options['appname'])) {
                        $attr['APP'] = $options['appname'];
                    }

                    // Add MSSQL specific configuration options
                    $config = [
                        'ApplicationIntent',
                        'AttachDBFileName',
                        'Authentication',
                        'ColumnEncryption',
                        'ConnectionPooling',
                        'Encrypt',
                        'Failover_Partner',
                        'KeyStoreAuthentication',
                        'KeyStorePrincipalId',
                        'KeyStoreSecret',
                        'LoginTimeout',
                        'MultipleActiveResultSets',
                        'MultiSubnetFailover',
                        'Scrollable',
                        'TraceFile',
                        'TraceOn',
                        'TransactionIsolation',
                        'TransparentNetworkIPResolution',
                        'TrustServerCertificate',
                        'WSID',
                    ];

                    foreach ($config as $value) {
                        $keyname = strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $value));

                        if (isset($options[$keyname])) {
                            $attr[$value] = $options[$keyname];
                        }
                    }
                }
                break;

            default:
                throw new InvalidArgumentException("Unsupported database type: {$this->type}");
        }

        if (!isset($attr['driver'])) {
            throw new InvalidArgumentException('Incorrect connection options.');
        }

        $driver = $attr['driver'];

        if (!in_array($driver, NativePDO::getAvailableDrivers())) {
            throw new InvalidArgumentException("Unsupported PDO driver: {$driver}.");
        }

        unset($attr['driver']);

        $stack = [];

        foreach ($attr as $key => $value) {
            $stack[] = is_int($key) ? $value : $key . '=' . $value;
        }

        return $driver . ':' . implode(';', $stack);
    }

    /**
     * Execute initialization commands for the database connection.
     *
     * @param array $options Connection options
     */
    protected function executeInitCommands(array $options): void
    {
        $commands = [];

        switch ($this->type) {
            case 'mysql':
                // Make MySQL use standard quoted identifier
                $commands[] = 'SET SQL_MODE=ANSI_QUOTES';
                break;

            case 'mssql':
                // Keep MSSQL QUOTED_IDENTIFIER ON for standard quoting
                $commands[] = 'SET QUOTED_IDENTIFIER ON';
                // Make ANSI_NULLS ON for NULL value
                $commands[] = 'SET ANSI_NULLS ON';
                break;
        }

        // Set character set
        if (
            in_array($this->type, ['mysql', 'pgsql', 'sybase', 'mssql']) &&
            isset($options['charset'])
        ) {
            $commands[] = "SET NAMES '{$options['charset']}'" . (
                $this->type === 'mysql' && isset($options['collation']) ?
                " COLLATE '{$options['collation']}'" : ''
            );
        }

        // Add custom commands if provided
        if (isset($options['command']) && is_array($options['command'])) {
            $commands = array_merge($commands, $options['command']);
        }

        // Execute all commands
        foreach ($commands as $command) {
            $this->pdo->exec($command);
        }
    }

    /**
     * Get the PDO instance.
     *
     * @return \PDO The PDO instance
     */
    public function getPdo(): NativePDO
    {
        return $this->pdo;
    }
    
    /**
     * Check if the database connection is active.
     *
     * @return bool True if connected, false otherwise
     */
    public function isConnected(): bool
    {
        return isset($this->pdo) && $this->pdo instanceof NativePDO;
    }

    /**
     * Get the database type.
     *
     * @return string The database type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the table prefix.
     *
     * @return string The table prefix
     */
    public function getPrefix(): string
    {
        return $this->prefix ?? '';
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
     * Get the last error details.
     *
     * @return array|null The last error details
     */
    public function getErrorInfo(): ?array
    {
        return $this->errorInfo;
    }

    /**
     * Execute a query and return the statement.
     *
     * @param string $query The SQL query
     * @param array $params The parameters to bind
     * @return PDOStatement|null The PDO statement
     */
    public function execute(string $query, array $params = []): ?PDOStatement
    {
        $this->statement = null;
        $this->error = null;
        $this->errorInfo = null;

        try {
            $this->statement = $this->pdo->prepare($query);
            $this->statement->execute($params);
            return $this->statement;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            $this->errorInfo = $e->errorInfo ?? null;
            return null;
        }
    }
}