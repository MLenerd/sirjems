<?php
session_start();
include "config/config.php";

// --- SECURITY: ADMIN ONLY ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php"); exit;
}
$timeout = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset(); session_destroy(); header("Location: login.php?error=timeout"); exit;
}
$_SESSION['last_activity'] = time();
// ----------------------

// --- HANDLE ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $target_id = intval($_POST['target_id']);
    $status = ($_POST['action'] === 'approve') ? 'approved' : 'rejected';
    mysqli_query($conn, "UPDATE users SET status = '$status' WHERE id = $target_id");
    header("Location: admin_users.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Users | Inventory System</title>
    <link rel="icon" href="img/ico/logo.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f4f4; }
        .navbar { background: #000 !important; }
        .navbar-brand, .nav-link { color: #fff !important; font-weight: 500; }
        .nav-link.active { color: #ffc107 !important; }
        .card-user { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); margin-bottom: 20px; transition: 0.3s; }
        .card-user:hover { transform: translateY(-5px); }
        .badge-pending { background: #fff3cd; color: #856404; } 
        .badge-approved { background: #d4edda; color: #155724; } 
        .badge-rejected { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top py-3">
        <div class="container-fluid">
            <a class="navbar-brand ps-4" href="index.php">
                <i class="fa-solid fa-warehouse me-2"></i>Inventory System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav me-4">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fa-solid fa-house me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="stock-tracking.php"><i class="fa-solid fa-boxes-stacked me-1"></i> Stock</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="warehouse-layout-optimization.php"><i class="fa-solid fa-map-location-dot me-1"></i> Layout</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inventory-valuation.php"><i class="fa-solid fa-chart-line me-1"></i> Valuation</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_users.php"><i class="fa-solid fa-users-gear me-1"></i> Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="backup_restore.php"><i class="fa-solid fa-database me-1"></i> Database</a>
                    </li>

                    <li class="nav-item ms-lg-3">
                        <a class="nav-link text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 120px;">
        <h2 class="fw-bold mb-4 animate__animated animate__fadeInLeft">User Management</h2>

        <h5 class="mb-3">Pending Requests</h5>
        <div class="row mb-5">
            <?php
            $pending = mysqli_query($conn, "SELECT * FROM users WHERE status = 'pending'");
            if (mysqli_num_rows($pending) > 0) {
                while($u = mysqli_fetch_assoc($pending)) {
            ?>
                <div class="col-md-4">
                    <div class="card-user animate__animated animate__fadeInUp">
                        <h5 class="fw-bold"><?php echo htmlspecialchars($u['first_name'].' '.$u['last_name']); ?></h5>
                        <p class="text-muted mb-2"><i class="fa-solid fa-envelope me-2"></i><?php echo htmlspecialchars($u['email']); ?></p>
                        <div class="mb-3"><span class="badge badge-pending rounded-pill px-3">Pending Approval</span></div>
                        <div class="d-flex gap-2">
                            <form method="POST" class="w-50"><input type="hidden" name="target_id" value="<?php echo $u['id']; ?>"><button name="action" value="approve" class="btn btn-success w-100 btn-sm"><i class="fa-solid fa-check me-1"></i> Approve</button></form>
                            <form method="POST" class="w-50"><input type="hidden" name="target_id" value="<?php echo $u['id']; ?>"><button name="action" value="reject" class="btn btn-outline-danger w-100 btn-sm"><i class="fa-solid fa-xmark me-1"></i> Reject</button></form>
                        </div>
                    </div>
                </div>
            <?php }} else { echo '<div class="col-12"><div class="alert alert-light border text-muted"><i class="fa-solid fa-circle-check me-2"></i>No pending requests.</div></div>'; } ?>
        </div>

        <h5 class="mb-3">All Accounts</h5>
        <div class="card border-0 shadow-sm animate__animated animate__fadeInUp">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark"><tr><th class="ps-4">Name</th><th>Email</th><th>Role</th><th>Status</th><th class="text-end pe-4">Action</th></tr></thead>
                    <tbody>
                        <?php
                        $all = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
                        while($r = mysqli_fetch_assoc($all)) {
                            $badge = ($r['status']=='approved')?'badge-approved':(($r['status']=='pending')?'badge-pending':'badge-rejected');
                            $roleClass = ($r['role']=='admin') ? 'bg-dark' : 'bg-secondary';
                        ?>
                        <tr>
                            <td class="fw-bold ps-4"><?php echo htmlspecialchars($r['first_name'].' '.$r['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($r['email']); ?></td>
                            <td><span class="badge <?php echo $roleClass; ?>"><?php echo strtoupper($r['role']); ?></span></td>
                            <td><span class="badge <?php echo $badge; ?>"><?php echo ucfirst($r['status']); ?></span></td>
                            <td class="text-end pe-4">
                                <?php if($r['role'] !== 'admin'): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Revoke user access?');">
                                    <input type="hidden" name="target_id" value="<?php echo $r['id']; ?>">
                                    <button name="action" value="reject" class="btn btn-sm btn-light text-danger border" title="Revoke Access"><i class="fa-solid fa-ban"></i></button>
                                </form>
                                <?php else: ?>
                                    <small class="text-muted fst-italic">Admin</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>