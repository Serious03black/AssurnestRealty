<?php
// migration_v2_driver_updates.php
require_once 'includes/db.php';

function executeQuery($pdo, $sql, $message) {
    try {
        $pdo->exec($sql);
        echo "Success: $message\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), 'referenced constraint name') !== false || strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "Skipped (Already exists): $message\n";
        } else {
            echo "Failed: $message - " . $e->getMessage() . "\n";
        }
    }
}

// 1. Add referral_code to cab_drivers
executeQuery($pdo, "ALTER TABLE cab_drivers ADD COLUMN referral_code VARCHAR(10) UNIQUE AFTER email", "Added referral_code to cab_drivers");

// 2. Modify referral_bonus in cab_drivers to DECIMAL(10,2)
executeQuery($pdo, "ALTER TABLE cab_drivers MODIFY COLUMN referral_bonus DECIMAL(10,2) DEFAULT 0.00", "Modified referral_bonus in cab_drivers");

// 3. Add prefix column to employees
executeQuery($pdo, "ALTER TABLE employees ADD COLUMN prefix VARCHAR(20) UNIQUE AFTER emp_id", "Added prefix to employees");

// 4. Set AUTO_INCREMENT for employees
executeQuery($pdo, "ALTER TABLE employees AUTO_INCREMENT = 12101", "Set AUTO_INCREMENT for employees");

// 5. Create Trigger for employees (Drop first to ensure update)
executeQuery($pdo, "DROP TRIGGER IF EXISTS before_insert_employees", "Dropped existing trigger before_insert_employees");

$trigger_employees = "
CREATE TRIGGER before_insert_employees
BEFORE INSERT ON employees
FOR EACH ROW
BEGIN
   SET NEW.prefix = CONCAT(
       'emp',
       (SELECT AUTO_INCREMENT
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'employees')
   );
END";
executeQuery($pdo, $trigger_employees, "Created trigger before_insert_employees");


// 6. Add prefix column to cab_drivers
executeQuery($pdo, "ALTER TABLE cab_drivers ADD COLUMN prefix VARCHAR(20) UNIQUE AFTER driver_id", "Added prefix to cab_drivers");

// 7. Set AUTO_INCREMENT for cab_drivers
executeQuery($pdo, "ALTER TABLE cab_drivers AUTO_INCREMENT = 122001", "Set AUTO_INCREMENT for cab_drivers");

// 8. Create Trigger for cab_drivers (Drop first)
executeQuery($pdo, "DROP TRIGGER IF EXISTS before_insert_cab_drivers", "Dropped existing trigger before_insert_cab_drivers");

$trigger_drivers = "
CREATE TRIGGER before_insert_cab_drivers
BEFORE INSERT ON cab_drivers
FOR EACH ROW
BEGIN
   SET NEW.prefix = CONCAT(
       'cab',
       (SELECT AUTO_INCREMENT
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'cab_drivers')
   );
END";
executeQuery($pdo, $trigger_drivers, "Created trigger before_insert_cab_drivers");

echo "Migration v2 completed.\n";
?>
