<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$msg_type = 'success';

// Handle Status Update (Approve/Reject)
if (isset($_POST['action']) && isset($_POST['id']) && isset($_POST['role'])) {
    $id     = (int)$_POST['id'];
    $role   = $_POST['role'];
    $action = $_POST['action'];
    $status = ($action === 'approve') ? 'approved' : 'rejected';

    if ($role === 'employee') {
        $pdo->prepare("UPDATE employees SET status = ? WHERE emp_id = ?")->execute([$status, $id]);
    } elseif ($role === 'driver') {
        $pdo->prepare("UPDATE cab_drivers SET status = ? WHERE driver_id = ?")->execute([$status, $id]);
    }
    $message = ucfirst($role) . " " . $status . " successfully!";
}

// ── Fetch Employees with commission stats ────────────────────────────
$employees_raw = $pdo->query("
    SELECT emp_id, prefix, emp_name, email, mobile_no, status, enrollment_date,
           total_properties_sold, commission
    FROM employees
    ORDER BY emp_id DESC
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($employees_raw as &$e) {
    // Total earned from property_sales (actual commission on sales)
    $s = $pdo->prepare("
        SELECT COALESCE(SUM(
            (CASE WHEN ps.sale_price > 0 THEN ps.sale_price ELSE p.price END)
            * p.commission / 100
        ), 0) as earned
        FROM property_sales ps
        JOIN properties p ON ps.property_id = p.property_id
        WHERE ps.emp_id = ?
    ");
    $s->execute([$e['emp_id']]);
    $e['total_earned'] = (float)$s->fetchColumn();

    $p = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE user_id=? AND role='employee'");
    $p->execute([$e['emp_id']]);
    $e['total_received'] = (float)$p->fetchColumn();
    $e['pending'] = $e['total_earned'] - $e['total_received'];
}
unset($e);

// ── Fetch Drivers ────────────────────────────────────────────────────
$drivers = $pdo->query("
    SELECT driver_id AS id, prefix, driver_name AS name, email, mobile_no,
           status, enrollment_date, total_properties_sold, referral_code
    FROM cab_drivers
    ORDER BY driver_id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ── Compute global rank for employees (by total_earned) ──────────────
$emp_sorted = $employees_raw;
usort($emp_sorted, fn($a,$b) => $b['total_earned'] <=> $a['total_earned']);
$emp_rank_map = [];
foreach ($emp_sorted as $pos => $e) {
    $emp_rank_map[$e['emp_id']] = $pos + 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Assurnest Realty Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        :root {
            --bg:       #0f1217;
            --card:     #161b22;
            --green:    #0f6b3a;
            --green-d:  #084d2a;
            --gold:     #d4af37;
            --blue:     #3b82f6;
            --red:      #ef4444;
            --text:     #e2e8f0;
            --muted:    #94a3b8;
            --border:   #2d3748;
            --sidebar:  260px;
            --nav:      70px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }

        .sidebar { width:var(--sidebar); background:linear-gradient(180deg,var(--green-d),var(--green)); height:100vh; position:fixed; left:0; top:0; z-index:1000; overflow-y:auto; }
        .navbar  { position:fixed; top:0; left:var(--sidebar); right:0; height:var(--nav); background:linear-gradient(90deg,var(--gold),#b8860b); display:flex; align-items:center; padding:0 30px; font-weight:700; font-size:1.1rem; z-index:999; box-shadow:0 4px 20px rgba(0,0,0,.4); }
        .main    { margin-left:var(--sidebar); margin-top:var(--nav); padding:2rem; }

        h1 { font-size:1.8rem; color:var(--gold); margin-bottom:1.5rem; }

        /* Tabs */
        .tabs { display:flex; gap:10px; margin-bottom:1.5rem; }
        .tab-btn { padding:10px 24px; border:2px solid var(--gold); border-radius:8px; background:transparent; color:var(--gold); font-weight:700; cursor:pointer; font-size:1rem; transition:.2s; }
        .tab-btn.active, .tab-btn:hover { background:var(--gold); color:#000; }
        .tab-panel { display:none; }
        .tab-panel.active { display:block; }

        /* Alert */
        .alert { padding:12px 18px; border-radius:8px; margin-bottom:20px; font-weight:600; }
        .alert-success { background:#052e16; color:#4ade80; border:1px solid #166534; }

        /* Table */
        .table-wrap { background:var(--card); border-radius:12px; overflow:hidden; border:1px solid var(--gold); }
        table { width:100%; border-collapse:collapse; }
        th { background:var(--green); color:white; padding:14px 16px; text-align:left; font-size:.9rem; text-transform:uppercase; letter-spacing:.5px; }
        td { padding:14px 16px; border-bottom:1px solid var(--border); font-size:.95rem; vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:rgba(255,255,255,.03); }

        /* Badges */
        .badge { padding:4px 10px; border-radius:20px; font-size:.8rem; font-weight:700; }
        .badge-approved { background:#052e16; color:#4ade80; }
        .badge-pending  { background:#422006; color:#fbbf24; }
        .badge-rejected { background:#450a0a; color:#f87171; }

        /* Rank badge */
        .rank { width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:1rem; color:#000; }
        .rank-1 { background:#ffd700; }
        .rank-2 { background:#c0c0c0; }
        .rank-3 { background:#cd7f32; }
        .rank-other { background:#4b5563; color:#e2e8f0; font-size:.85rem; }

        /* Commission numbers */
        .text-green { color:#22c55e; font-weight:700; }
        .text-blue  { color:#60a5fa; }
        .text-red   { color:#f87171; font-weight:700; }

        /* Action buttons */
        .btn { padding:6px 12px; border:none; border-radius:6px; cursor:pointer; font-size:.87rem; font-weight:600; display:inline-flex; align-items:center; gap:5px; text-decoration:none; }
        .btn-view    { background:var(--blue); color:#fff; }
        .btn-view:hover { background:#2563eb; }
        .btn-approve { background:#16a34a; color:#fff; }
        .btn-approve:hover { background:#15803d; }
        .btn-reject  { background:#dc2626; color:#fff; }
        .btn-reject:hover  { background:#b91c1c; }

        .actions { display:flex; gap:6px; flex-wrap:wrap; align-items:center; }

        @media(max-width:992px) { .main { padding: 1rem; } }
    </style>
</head>
<body>

<?php include '../../includes/navbaradmin.php'; ?>

<div class="main">
    <h1><i class="fas fa-users-cog"></i> Manage Users</h1>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('employees',this)">
            <i class="fas fa-user-tie"></i> Employees (<?= count($employees_raw) ?>)
        </button>
        <button class="tab-btn" onclick="switchTab('drivers',this)">
            <i class="fas fa-car"></i> Cab Drivers (<?= count($drivers) ?>)
        </button>
    </div>

    <!-- ══ EMPLOYEES TAB ══════════════════════════════════════════════ -->
    <div id="tab-employees" class="tab-panel active">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email / Mobile</th>
                        <th>Joining Date</th>
                        <th>Sold</th>
                        <th>Total Earned</th>
                        <th>Pending</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees_raw as $e): ?>
                        <?php $rank = $emp_rank_map[$e['emp_id']] ?? '—'; ?>
                        <tr>
                            <td>
                                <?php
                                $rc = is_numeric($rank) && $rank <= 3 ? "rank-$rank" : 'rank-other';
                                ?>
                                <span class="rank <?= $rc ?>"><?= $rank ?></span>
                            </td>
                            <td><small style="color:var(--muted);"><?= htmlspecialchars($e['prefix'] ?? $e['emp_id']) ?></small></td>
                            <td><strong><?= htmlspecialchars($e['emp_name']) ?></strong></td>
                            <td>
                                <?= htmlspecialchars($e['email']) ?><br>
                                <small style="color:var(--muted);"><?= htmlspecialchars($e['mobile_no']) ?></small>
                            </td>
                            <td><?= $e['enrollment_date'] ? date('d M Y', strtotime($e['enrollment_date'])) : '—' ?></td>
                            <td style="text-align:center;"><?= (int)$e['total_properties_sold'] ?></td>
                            <td class="text-green">₹ <?= number_format($e['total_earned'], 0) ?></td>
                            <td class="<?= $e['pending'] > 0 ? 'text-red' : 'text-blue' ?>">
                                ₹ <?= number_format(max(0,$e['pending']), 0) ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $e['status'] ?>">
                                    <?= ucfirst($e['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="employee_detail.php?id=<?= $e['emp_id'] ?>" class="btn btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if ($e['status'] !== 'approved'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $e['emp_id'] ?>">
                                            <input type="hidden" name="role" value="employee">
                                            <input type="hidden" name="action" value="approve">
                                            <button class="btn btn-approve" type="submit"><i class="fas fa-check"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($e['status'] !== 'rejected'): ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Reject this employee?');">
                                            <input type="hidden" name="id" value="<?= $e['emp_id'] ?>">
                                            <input type="hidden" name="role" value="employee">
                                            <input type="hidden" name="action" value="reject">
                                            <button class="btn btn-reject" type="submit"><i class="fas fa-times"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($employees_raw)): ?>
                        <tr><td colspan="10" style="text-align:center; padding:2rem; color:var(--muted);">No employees found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ══ DRIVERS TAB ════════════════════════════════════════════════ -->
    <div id="tab-drivers" class="tab-panel">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email / Mobile</th>
                        <th>Referral Code</th>
                        <th>Joining Date</th>
                        <th>Sold</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($drivers as $d): ?>
                        <tr>
                            <td><small style="color:var(--muted);"><?= htmlspecialchars($d['prefix'] ?? $d['id']) ?></small></td>
                            <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
                            <td>
                                <?= htmlspecialchars($d['email']) ?><br>
                                <small style="color:var(--muted);"><?= htmlspecialchars($d['mobile_no']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($d['referral_code'] ?? '—') ?></td>
                            <td><?= $d['enrollment_date'] ? date('d M Y', strtotime($d['enrollment_date'])) : '—' ?></td>
                            <td style="text-align:center;"><?= (int)$d['total_properties_sold'] ?></td>
                            <td>
                                <span class="badge badge-<?= $d['status'] ?>">
                                    <?= ucfirst($d['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <?php if ($d['status'] !== 'approved'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                            <input type="hidden" name="role" value="driver">
                                            <input type="hidden" name="action" value="approve">
                                            <button class="btn btn-approve" type="submit"><i class="fas fa-check"></i> Approve</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($d['status'] !== 'rejected'): ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Reject this driver?');">
                                            <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                            <input type="hidden" name="role" value="driver">
                                            <input type="hidden" name="action" value="reject">
                                            <button class="btn btn-reject" type="submit"><i class="fas fa-times"></i> Reject</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($drivers)): ?>
                        <tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--muted);">No drivers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function switchTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}
</script>
</body>
</html>