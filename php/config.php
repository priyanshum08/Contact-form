<?php
// ── Database Configuration ────────────────────────────────────────────────────
// Adjust these values to match your local environment (XAMPP/MAMP/WAMP)

define('DB_HOST',   'localhost');
define('DB_NAME',   'contact_project');
define('DB_USER',   'root');
define('DB_PASS',   '');          // MAMP default: 'root' | XAMPP default: ''
define('DB_CHARSET','utf8mb4');

// ── Admin Credentials ─────────────────────────────────────────────────────────
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

// ── PDO Connection Factory ────────────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'status'  => 'error',
            'message' => 'Database connection failed: ' . $e->getMessage()
        ]);
        exit;
    }

    return $pdo;
}
