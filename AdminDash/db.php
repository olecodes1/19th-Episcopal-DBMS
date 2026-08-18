<?php
// Session security configuration
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 3600); // 1 hour session timeout
session_set_cookie_params([
    'lifetime' => 3600, // 1 hour
    'path' => '/',
    'domain' => '',
    'secure' => false, // Set to true when using HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Database configuration from environment variables
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: '19edypd_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

$isDebug = getenv('APP_DEBUG') === '1';
ini_set('display_errors', $isDebug ? '1' : '0');
ini_set('display_startup_errors', $isDebug ? '1' : '0');

try {
    $socket = getenv('DB_SOCKET') ?: '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
    if (file_exists($socket)) {
        $dsn = "mysql:unix_socket=$socket;dbname=$dbname;charset=utf8mb4";
    } else {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    }
    $pdo = new PDO($dsn, $username, $password);
    // Set PDO to throw exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Disable emulated prepares for better security
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    // Set default fetch mode
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    if (getenv('APP_DEBUG') === '1') {
        echo "Database connection failed: " . htmlspecialchars($e->getMessage());
    } else {
        echo "Database connection failed. Please contact administrator.";
    }
    exit;
}

// Access control:
// - Admin surface requires authentication by default.
// - Guest/public pages must define ALLOW_GUEST=true before requiring db.php.
if (!defined('ALLOW_GUEST') || ALLOW_GUEST !== true) {
    require_once __DIR__ . '/includes/auth.php';
    require_auth();
}
