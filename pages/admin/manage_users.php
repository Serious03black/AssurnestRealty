<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';

// Handle Status Update (Approve/Reject)
if (isset($_POST['action']) && isset($_POST['id']) && isset($_POST['role'])) {
    $id = (int)$_POST['id'];
    $role = $_POST['role'];
    $action = $_POST['action']; // 'approve' or 'reject'
    $status = ($action === 'approve') ? 'approved' : 'rejected';

    if ($role === 'employee') {
        $stmt = $pdo->prepare("UPDATE employees SET status = ? WHERE emp_id = ?");
        $stmt->execute([$status, $id]);
    } elseif ($role === 'driver') {
        $stmt = $pdo->prepare("UPDATE cab_drivers SET status = ? WHERE driver_id = ?");
        $stmt->execute([$status, $id]);
    }
    $message = ucfirst($role) . " " . $status . " successfully!";
}

// Fetch Employees and Drivers
$employees = $pdo->query("SELECT emp_id AS id, emp_name AS name, email, mobile_no, status, 'employee' AS role FROM employees ORDER BY emp_id DESC")->fetchAll(PDO::FETCH_ASSOC);
$drivers = $pdo->query("SELECT driver_id AS id, driver_name AS name, email, mobile_no, status, 'driver' AS role, referral_code FROM cab_drivers ORDER BY driver_id DESC")->fetchAll(PDO::FETCH_ASSOC);

$users = array_merge($employees, $drivers);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Assurnest Realty Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .sidebar { width: 250px; background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); color: white; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; }
        .main-content { margin-left: 250px; padding: 2rem; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-top: 20px; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #2a5bd7; color: white; }
        
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .btn { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; color: white; margin-right: 5px; }
        .btn-approve { background: #28a745; }
        .btn-reject { background: #dc3545; }
        
        @media (max-width: 768px) { .main-content { margin-left: 0; } .sidebar { display: none; } }
    </style>
</head>
<body>

<nav class="sidebar">
    <?php include '../../includes/sidebaradmin.php'; ?>
</nav>

<div class="main-content">
    <h1>Manage Users</h1>
    
    <?php if($message): ?>
        <div style="background:#d4edda; color:#155724; padding:10px; margin-bottom:20px; border-radius:5px;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <h2>Pending Approvals & All Users</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Email / Mobile</th>
                <th>Referral Code</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= ucfirst($u['role']) ?></td>
                    <td>
                        <?= htmlspecialchars($u['email']) ?><br>
                        <small><?= htmlspecialchars($u['mobile_no']) ?></small>
                    </td>
                    <td><?= isset($u['referral_code']) ? htmlspecialchars($u['referral_code']) : '—' ?></td>
                    <td>
                        <span class="status-badge status-<?= $u['status'] ?>">
                            <?= ucfirst($u['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if($u['status'] === 'pending' || $u['status'] === 'rejected'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="role" value="<?= $u['role'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-approve">Approve</button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if($u['status'] === 'pending' || $u['status'] === 'approved'): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Reject this user?');">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="role" value="<?= $u['role'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-reject">Reject</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>