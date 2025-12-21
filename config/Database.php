<?php
/**
 * HireGenius - Database Connection Class
 * 
 * Handles database connection with singleton pattern
 */

class Database
{
    private static ?Database $instance = null;
    private ?mysqli $connection = null;
    private array $config;

    private function __construct()
    {
        $this->loadConfig();
        $this->connect();
    }

    /**
     * Get database instance (Singleton)
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load configuration
     */
    private function loadConfig(): void
    {
        $configFile = __DIR__ . '/config.php';
        
        if (!file_exists($configFile)) {
            die('Configuration file not found. Please copy config.example.php to config.php');
        }
        
        $this->config = require $configFile;
    }

    /**
     * Connect to database
     */
    private function connect(): void
    {
        $db = $this->config['database'];
        
        $this->connection = new mysqli(
            $db['host'],
            $db['username'],
            $db['password'],
            $db['database']
        );

        if ($this->connection->connect_error) {
            die('Database connection failed: ' . $this->connection->connect_error);
        }

        $this->connection->set_charset($db['charset'] ?? 'utf8mb4');
    }

    /**
     * Get connection
     */
    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    /**
     * Get config value
     */
    public function getConfig(string $key = null)
    {
        if ($key === null) {
            return $this->config;
        }
        
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return null;
            }
        }
        
        return $value;
    }

    /**
     * Close connection
     */
    public function close(): void
    {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }
}
