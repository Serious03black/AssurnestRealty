<?php
// ────────────────────────────────────────────────
//          Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'property_sales_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// First connect without database to check/create it
try {
    $temp_pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $temp_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if database exists
    $check_db = $temp_pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
    $check_db->execute([DB_NAME]);
    
    if ($check_db->rowCount() == 0) {
        // Create database
        $temp_pdo->exec("CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    
    // Now connect to the database
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// Base URL for the application
define('BASE_URL', 'http://localhost/real-estate-app1/');
?>