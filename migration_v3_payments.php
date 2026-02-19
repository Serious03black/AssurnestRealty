<?php
// migration_v3_payments.php
require_once 'includes/db.php';

function executeQuery($pdo, $sql, $message) {
    try {
        $pdo->exec($sql);
        echo "Success: $message\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'Duplicate column name') !== false) {
             echo "Skipped (Already exists): $message\n";
        } else {
             echo "Failed: $message - " . $e->getMessage() . "\n";
        }
    }
}

// 1. Create payments table
$sql = "
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('employee', 'driver') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(255) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    INDEX (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

executeQuery($pdo, $sql, "Created payments table");

echo "Migration v3 completed.\n";
?>
