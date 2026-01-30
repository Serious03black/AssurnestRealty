<?php

// ────────────────────────────────────────────────
//          Database (your existing config)
define('DB_HOST', 'localhost');
define('DB_NAME', 'real_estate_db');
define('DB_USER', 'root');
define('DB_PASS', '');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// ────────────────────────────────────────────────
//          Cloudinary Configuration
require_once __DIR__ . '/vendor/autoload.php';

use Cloudinary\Cloudinary;

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dmwvemylm',      // ← from Cloudinary dashboard
        'api_key'    => '599218289944345',         // ← from dashboard
        'api_secret' => '5HZA9yVWNSPHF-c3Y6ldtlWTMpw',      // ← from dashboard (keep secret!)
    ],
    'url' => [
        'secure' => true                        // Use https URLs
    ]
]);

// Helper function to use anywhere
function cloudinary() {
    global $cloudinary;
    return $cloudinary;
}
?>