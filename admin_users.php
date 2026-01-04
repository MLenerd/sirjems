<?php
session_start();
include "config/config.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php"); exit;
}
$timeout = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset(); session_destroy(); header("Location: login.php?error=timeout"); exit;
}
$_SESSION['last_activity'] = time();

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
        body { 
            font-family: 'Inter', sans-serif; 
            background: #ffffff; /* White background */
            color: #02381e; /* Dark green text */
        }
        .navbar { 
            background: #02381e !important; /* Dark green navbar */
        }
        .navbar-brand, .nav-link { 
            color: #ffffff !important; /* White text */
            font-weight: 500; 
        }
        .nav-link.active, .nav-link:focus, .nav-link:hover { 
            color: #c19802 !important; /* Gold for active/hover */
        }
        .navbar-brand { 
            font-size: 20px; 
            font-weight: 700; 
            color: #c19802 !important; /* Gold brand text */
        }
        .card-user { 
            background: #ffffff; /* White background */
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(2, 56, 30, 0.15); /* Dark green shadow */
            margin-bottom: 20px; 
            transition: 0.3s; 
            border: 2px solid #02381e; /* Dark green border */
        }
        .card-user:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 12px 20px rgba(2, 56, 30, 0.25);
            border-color: #c19802; /* Gold border on hover */
        }
        .badge-pending { 
            background: rgba(193, 152, 2, 0.15); /* Gold with transparency */
            color: #c19802; /* Gold text */
            border: 1px solid rgba(193, 152, 2, 0.3);
        } 
        .badge-approved { 
            background: rgba(2, 56, 30, 0.15); /* Dark green with transparency */
            color: #02381e; /* Dark green text */
            border: 1px solid rgba(2, 56, 30, 0.3);
        } 
        .badge-rejected { 
            background: rgba(220, 53, 69, 0.15); /* Red with transparency */
            color: #dc3545; /* Red text */
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        .nav-link { margin-right: 15px; }
        
        /* Button Styling */
        .btn-success {
            background-color: #02381e !important; /* Dark green */
            border-color: #02381e !important;
            color: #ffffff !important;
        }
        .btn-success:hover {
            background-color: #012916 !important; /* Darker green */
            border-color: #012916 !important;
        }
        
        .btn-outline-danger {
            color: #dc3545 !important;
            border-color: #dc3545 !important;
        }
        .btn-outline-danger:hover {
            background-color: #dc3545 !important;
            color: #ffffff !important;
        }
        
        .btn-light {
            background-color: rgba(2, 56, 30, 0.1) !important;
            border: 1px solid rgba(2, 56, 30, 0.3) !important;
            color: #02381e !important;
        }
        .btn-light:hover {
            background-color: rgba(2, 56, 30, 0.2) !important;
        }
        
        /* Table Styling */
        .table-dark {
            background-color: #02381e !important; /* Dark green header */
            color: #ffffff !important;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(2, 56, 30, 0.05); /* Light green hover */
        }
        
        .card {
            border: 2px solid #02381e; /* Dark green border */
            background: #ffffff; /* White background */
            box-shadow: 0 4px 12px rgba(2, 56, 30, 0.15); /* Dark green shadow */
        }
        
        /* Badge for roles */
        .bg-dark {
            background-color: #02381e !important; /* Dark green for admin */
            color: #ffffff !important;
        }
        .bg-secondary {
            background-color: #c19802 !important; /* Gold for user */
            color: #ffffff !important;
        }
        
        /* Alert Styling */
        .alert-light {
            background-color: rgba(2, 56, 30, 0.1);
            border: 1px solid rgba(2, 56, 30, 0.3);
            color: #02381e;
        }
        
        /* Headings */
        h2, h5 {
            color: #02381e; /* Dark green */
        }
        
        /* Logout link */
        .nav-link.text-danger {
            color: #c19802 !important; /* Gold for logout */
        }
        .nav-link.text-danger:hover {
            color: #02381e !important; /* Dark green on hover */
        }
        
        /* Container spacing */
        .container {
            margin-top: 120px;
        }
        
        /* Text muted */
        .text-muted {
            color: rgba(2, 56, 30, 0.7) !important;
        }
        
        /* Check icon color */
        .fa-circle-check {
            color: #c19802; /* Gold */
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top py-3">
        <div class="container-fluid">
            <a class="navbar-brand ps-4" href="index.php" style="color: #c19802 !important;">
                <img src="img/logo.jpg" alt="Logo" height="30" class="me-2">
                Puregold Inventory System
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

    <div class="container">
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
    <script>
        const timeoutDuration = <?php echo $timeout; ?>;
        const timeoutLimit = timeoutDuration * 1000;
        let idleTimer;

        function resetIdleTimer() {
            clearTimeout(idleTimer);

            idleTimer = setTimeout(function() {
                alert("Idled for too long");
                window.location.href = 'logout.php?reason=timeout'; 
            }, timeoutLimit);
        }

        window.addEventListener('mousemove', resetIdleTimer);
        window.addEventListener('keypress', resetIdleTimer);
        window.addEventListener('click', resetIdleTimer);
        window.addEventListener('scroll', resetIdleTimer);

        resetIdleTimer();
    </script>
</body>
</html>