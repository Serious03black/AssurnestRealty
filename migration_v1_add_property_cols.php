<?php
// migration_v1_add_property_cols.php
require_once 'includes/db.php';

try {
    $pdo->exec("ALTER TABLE properties
        ADD COLUMN sqft DECIMAL(10,2) AFTER commission,
        ADD COLUMN kitchens INT DEFAULT 0,
        ADD COLUMN rooms INT DEFAULT 0,
        ADD COLUMN bathrooms INT DEFAULT 0;");
    echo "Migration successful: Added sqft, kitchens, rooms, bathrooms columns.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Migration skipped: Columns already exist.\n";
    } else {
        echo "Migration failed: " . $e->getMessage() . "\n";
    }
}
?>
