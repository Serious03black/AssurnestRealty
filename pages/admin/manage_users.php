<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';

// Handle DELETE
if (isset($_POST['delete']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    if ($user_id !== $_SESSION['user_id']) { // prevent self-delete
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        $message = "User deleted successfully!";
    } else {
        $message = "You cannot delete your own account.";
    }
}

// Handle EDIT
if (isset($_POST['edit_user']) && isset($_POST['user_id'])) {
    $user_id   = (int)$_POST['user_id'];
    $username  = trim($_POST['username'] ?? '');
    $password  = trim($_POST['password'] ?? ''); // only update if not empty
    $role      = $_POST['role'] ?? 'user';

    if (empty($username)) {
        $message = "Username cannot be empty.";
    } else {
        $update = "UPDATE users SET username = ?, role = ?";
        $params = [$username, $role];

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update .= ", password = ?";
            $params[] = $hashed_password;
        }

        $update .= " WHERE id = ? AND id != ?";
        $params[] = $user_id;
        $params[] = $_SESSION['user_id'];

        $stmt = $pdo->prepare($update);
        $stmt->execute($params);
        $message = "User updated successfully!";
    }
}

// Fetch all users (only required fields)
$stmt = $pdo->prepare("
    SELECT id, username, role 
    FROM users 
    WHERE id != ? 
    ORDER BY id DESC
");
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Assurnest Realty Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        body {font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
, sans-serif; background: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        h1 { text-align: center; margin-bottom: 2rem;  margin-top:10%}
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .success { background: #d4edda; color: #155724; }
        table {
            width: 70%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-left:10%;
            /* margin-top:60px; */

        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th { background: #2a5bd7; color: white; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            margin: 0 0.3rem;
        }
        .btn-view    { background: #17a2b8; color: white; }
        .btn-edit    { background: #ffc107; color: #212529; }
        .btn-delete  { background: #dc3545; color: white; }
        .btn-view:hover    { background: #138496; }
        .btn-edit:hover    { background: #e0a800; }
        .btn-delete:hover  { background: #c82333; }
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
        }
        .form-group { margin-bottom: 1.2rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input, select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        button[type="submit"] {
            background: #2a5bd7;
            color: white;
            padding: 0.9rem 1.8rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        button[type="submit"]:hover { background: #1e4bb9; }
    </style>
</head>
<body>

<?php include '../../includes/sidebaradmin.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="container">

    <h1>Manage Users</h1>

    <?php if ($message): ?>
        <div class="message success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                    <td>
                        <!-- View Profile -->
                        <a href="user_profile.php?id=<?= $user['id'] ?>" class="btn btn-view">
                            <i class="fas fa-user"></i> Profile
                        </a>

                        <!-- Edit -->
                        <button class="btn btn-edit" onclick="openEditModal(<?= $user['id'] ?>, '<?= addslashes($user['username']) ?>', '<?= $user['role'] ?>')">
                            <i class="fas fa-edit"></i> Edit
                        </button>

                        <!-- Delete (only if not self) -->
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete user <?= addslashes($user['username']) ?>? This cannot be undone.');">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" name="delete" class="btn btn-delete">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h2>Edit User</h2>
        <span onclick="document.getElementById('editModal').style.display='none'" style="float:right; font-size:2rem; cursor:pointer;">×</span>

        <form method="POST">
            <input type="hidden" name="user_id" id="edit_user_id">
            <input type="hidden" name="edit_user" value="1">

            <div class="form-group">
                <label>Username</label>
                <input type="text" id="edit_username" name="username" required>
            </div>

            <div class="form-group">
                <label>New Password (leave blank to keep current)</label>
                <input type="password" name="password" placeholder="••••••••">
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" id="edit_role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openEditModal(id, username, role) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_role').value = role;
    document.getElementById('editModal').style.display = 'flex';
}
</script>

</body>
</html>