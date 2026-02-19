<?php
// pages/admin/process_payment.php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $role    = $_POST['role'] ?? '';
    $amount  = $_POST['amount'] ?? 0;
    $txn_id  = $_POST['transaction_id'] ?? '';
    $notes   = $_POST['notes'] ?? '';

    if ($user_id && $amount > 0 && ($role === 'employee' || $role === 'driver')) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO payments (user_id, role, amount, transaction_id, notes, payment_date)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$user_id, $role, $amount, $txn_id, $notes]);
            
            header('Location: manage_commissions.php?success=Payment recorded successfully');
            exit;
        } catch (PDOException $e) {
            header('Location: manage_commissions.php?error=Database error: ' . urlencode($e->getMessage()));
            exit;
        }
    } else {
        header('Location: manage_commissions.php?error=Invalid input data');
        exit;
    }
} else {
    header('Location: manage_commissions.php');
    exit;
}
?>
