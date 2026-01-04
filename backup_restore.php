<?php
session_start();
include "config/config.php";

// --- SECURITY: ADMIN ONLY ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: index.php"); exit; }
$timeout = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) { session_unset(); session_destroy(); header("Location: login.php?error=timeout"); exit; }
$_SESSION['last_activity'] = time();
// ----------------------

$msg = ""; $msg_type = "";

// BACKUP
if (isset($_POST['backup_db'])) {
    $tables = array();
    $result = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) { $tables[] = $row[0]; }
    $sqlScript = "";
    foreach ($tables as $table) {
        $create = mysqli_fetch_row(mysqli_query($conn, "SHOW CREATE TABLE $table"));
        $sqlScript .= "\n\n" . $create[1] . ";\n\n";
        $data = mysqli_query($conn, "SELECT * FROM $table");
        $colCount = mysqli_num_fields($data);
        while ($row = mysqli_fetch_row($data)) {
            $sqlScript .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $colCount; $j++) {
                $row[$j] = isset($row[$j]) ? mysqli_real_escape_string($conn, $row[$j]) : "";
                $sqlScript .= '"' . $row[$j] . '"' . ($j < ($colCount - 1) ? ',' : '');
            }
            $sqlScript .= ");\n";
        }
    }
    if(!empty($sqlScript)) {
        $file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=\"$file\"");
        echo $sqlScript; exit;
    }
}

// RESTORE
if (isset($_POST['restore_db'])) {
    if (!empty($_FILES['sql_file']['name'])) {
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
        $tables = mysqli_query($conn, "SHOW TABLES");
        while ($row = mysqli_fetch_row($tables)) { mysqli_query($conn, "DROP TABLE IF EXISTS $row[0]"); }
        
        $sqlContent = file_get_contents($_FILES['sql_file']['tmp_name']);
        $queries = explode(';', $sqlContent);
        foreach($queries as $query) { if(trim($query) != "") { mysqli_query($conn, $query); } }
        
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");
        $msg = "Database restored successfully."; $msg_type = "success";
    } else { $msg = "Invalid file."; $msg_type = "danger"; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database | Inventory System</title>
    <link rel="icon" href="img/ico/logo.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f4f4; }
        .navbar { background: #000 !important; }
        .navbar-brand, .nav-link { color: #fff !important; font-weight: 500; }
        .nav-link.active { color: #ffc107 !important; }
        .card-db { border: none; border-radius: 12px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: 0.3s; background: #fff; }
        .card-db:hover { transform: translateY(-5px); }
        .nav-link { margin-right: 15px; }
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

                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_users.php"><i class="fa-solid fa-users-gear me-1"></i> Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="backup_restore.php"><i class="fa-solid fa-database me-1"></i> Database</a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item ms-lg-3">
                        <a class="nav-link text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
                    </li>
                    
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 120px;">
        <h2 class="fw-bold mb-5">Database Management</h2>

        <?php if($msg): ?>
            <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show">
                <?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card card-db h-100">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-download fa-4x text-primary mb-3"></i>
                        <h4>Backup Database</h4>
                        <p class="text-muted">Download a complete SQL file of your current inventory.</p>
                    </div>
                    <form method="POST" class="mt-auto">
                        <button type="submit" name="backup_db" class="btn btn-primary w-100 py-2 fw-bold"><i class="fa-solid fa-file-arrow-down me-2"></i>Download Backup</button>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-db h-100">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-upload fa-4x text-danger mb-3"></i>
                        <h4>Restore Database</h4>
                        <p class="text-muted">Upload an SQL file to restore data. <span class="text-danger fw-bold">Warning: Overwrites everything.</span></p>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="mt-auto">
                        <input type="file" name="sql_file" class="form-control mb-3" accept=".sql" required>
                        <button type="submit" name="restore_db" class="btn btn-danger w-100 py-2 fw-bold" onclick="return confirm('WARNING: This will delete all current data. Continue?');"><i class="fa-solid fa-rotate-left me-2"></i>Restore Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>