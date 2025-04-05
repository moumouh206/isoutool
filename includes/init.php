<?php
// Define the root path
define('ROOT_PATH', dirname(__DIR__));

// Include configuration
require_once ROOT_PATH . '/config/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to get base URL
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    return "{$protocol}://{$host}{$path}";
}

// Set base URL
define('BASE_URL', rtrim(getBaseUrl(), '/'));

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require_once ROOT_PATH . '/database/db_connect.php';

// Cart class
require_once ROOT_PATH . '/includes/Cart.php';

// Initialize database connection
$database = new Database();
$db = $database->connect();

// Initialize cart if needed
if (isset($db)) {
    $cart = new Cart($db, $_SESSION['user_id'] ?? null);
} 