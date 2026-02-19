<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['employee', 'driver'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: availableProperty.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$property_id = $_POST['property_id'] ?? 0;
$sale_price = $_POST['sale_price'] ?? 0;

if (!$property_id) {
    die("Invalid property.");
}

try {
    $pdo->beginTransaction();

    // 1. Get Property Details & Lock Row
    $stmt = $pdo->prepare("SELECT price, commission, status FROM properties WHERE property_id = ? FOR UPDATE");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property || $property['status'] !== 'available') {
        throw new Exception("Property is no longer available.");
    }

    // Use property price if sale price not set or invalid (simple logic)
    if ($sale_price <= 0) {
        $sale_price = $property['price'];
    }

    $commission_rate = $property['commission'];
    $commission_amount = ($sale_price * $commission_rate) / 100;
    $date = date('Y-m-d');

    // 2. Mark Property as Sold
    $upd_stmt = $pdo->prepare("UPDATE properties SET status = 'sold' WHERE property_id = ?");
    $upd_stmt->execute([$property_id]);

    // 3. Record Sale & Update Seller Stats
    if ($role === 'employee') {
        // Insert Sale
        $sale_stmt = $pdo->prepare("
            INSERT INTO property_sales (property_id, emp_id, sale_date, sale_price) 
            VALUES (?, ?, ?, ?)
        ");
        $sale_stmt->execute([$property_id, $user_id, $date, $sale_price]);

        // Update Employee Stats
        $emp_upd = $pdo->prepare("
            UPDATE employees 
            SET total_properties_sold = total_properties_sold + 1, 
                commission = commission + ? 
            WHERE emp_id = ?
        ");
        $emp_upd->execute([$commission_amount, $user_id]);

    } else {
        // Driver
        // Insert Sale
        $sale_stmt = $pdo->prepare("
            INSERT INTO property_sales (property_id, driver_id, sale_date, sale_price) 
            VALUES (?, ?, ?, ?)
        ");
        $sale_stmt->execute([$property_id, $user_id, $date, $sale_price]);

        // Update Driver Stats
        $driver_upd = $pdo->prepare("
            UPDATE cab_drivers 
            SET total_properties_sold = total_properties_sold + 1, 
                commission = commission + ? 
            WHERE driver_id = ?
        ");
        $driver_upd->execute([$commission_amount, $user_id]);

        // 4. Referral Bonus Logic (Only for Drivers)
        // Get referrer
        $ref_stmt = $pdo->prepare("SELECT referral_id FROM cab_drivers WHERE driver_id = ?");
        $ref_stmt->execute([$user_id]);
        $driver_info = $ref_stmt->fetch(PDO::FETCH_ASSOC);

        if ($driver_info && $driver_info['referral_id']) {
            $bonus_amount = $commission_amount * 0.50; // 50% of commission
            
            // Update Referrer
            $referrer_upd = $pdo->prepare("
                UPDATE cab_drivers 
                SET referral_bonus = referral_bonus + ? 
                WHERE driver_id = ?
            ");
            $referrer_upd->execute([$bonus_amount, $driver_info['referral_id']]);
        }
    }

    $pdo->commit();
    header("Location: my_sales.php?status=success");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Sale failed: " . $e->getMessage());
}
?>
