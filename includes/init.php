<?php
/**
 * HireGenius - Application Bootstrap
 * 
 * Initialize application, load dependencies
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load required files
require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/includes/helpers.php';

// Start session
startSecureSession();

// Set timezone
date_default_timezone_set('UTC');

/**
 * Get database connection
 */
function db(): mysqli
{
    return Database::getInstance()->getConnection();
}

/**
 * Get config value
 */
function config(string $key = null)
{
    return Database::getInstance()->getConfig($key);
}
