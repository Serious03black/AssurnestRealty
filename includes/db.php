<?php
// ────────────────────────────────────────────────
//          Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'real_estate_db');
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

// Create properties table if it doesn't exist
$table_sql = "CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    description TEXT,
    bedrooms INT,
    bathrooms INT,
    square_feet INT,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

try {
    $pdo->exec($table_sql);
    
    // Check if table is empty and insert sample data
    $check = $pdo->query("SELECT COUNT(*) as count FROM properties");
    $row = $check->fetch();
    
    if ($row['count'] == 0) {
        $sample_data_sql = "INSERT INTO properties (title, location, price, description, bedrooms, bathrooms, square_feet, image_url) VALUES
            ('Modern Villa with Ocean View', 'Malibu, California', 3850000, 'A stunning contemporary villa featuring panoramic ocean views, infinity pool, and smart home technology.', 5, 6, 6500, 'https://images.unsplash.com/photo-1613977257363-707ba9348227?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80'),
            ('Luxury Manhattan Penthouse', 'New York, NY', 8200000, 'An exclusive penthouse in the heart of Manhattan with private elevator, rooftop terrace, and premium finishes.', 4, 5, 4200, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1171&q=80'),
            ('Alpine Mountain Retreat', 'Aspen, Colorado', 2950000, 'A luxurious mountain retreat with ski-in/ski-out access, heated floors, and breathtaking mountain views.', 6, 7, 8000, 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80')";
        
        $pdo->exec($sample_data_sql);
    }
    
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}

// ────────────────────────────────────────────────
//          Cloudinary Configuration
// Note: Make sure you've installed Cloudinary SDK via composer
// Run: composer require cloudinary/cloudinary_php
require_once __DIR__ . '/vendor/autoload.php';

use Cloudinary\Cloudinary;

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dz37qeiet',
        'api_key'    => '175172565564946',
        'api_secret' => 'k1QXmMN8zg-50Po5MWGqPE_fRRM',
    ],
    'url' => [
        'secure' => true
    ]
]);

// Helper function to use anywhere
function cloudinary() {
    global $cloudinary;
    return $cloudinary;
}


// Base URL for the application
define('BASE_URL', 'http://localhost/real-estate-app');
?>