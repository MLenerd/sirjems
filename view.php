<?php
session_start();
include "config/config.php";

// --- SECURITY BLOCK ---
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$timeout = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset(); session_destroy(); header("Location: login.php?error=timeout"); exit;
}
$_SESSION['last_activity'] = time();
// ----------------------

$sku = isset($_GET['barcode']) ? mysqli_real_escape_string($conn, $_GET['barcode']) : '';
$itemFound = false;
$data = [];

if (!empty($sku)) {
    // UPDATED QUERY: Fetches data from stocks, racks, and allocations
    $query = "
        SELECT 
            s.item, s.bar, s.category, s.price,
            COALESCE(SUM(ra.quantity), 0) as total_qty,
            GROUP_CONCAT(r.name SEPARATOR ', ') as loc_list
        FROM stocks s
        LEFT JOIN rack_allocations ra ON s.id = ra.stock_id
        LEFT JOIN racks r ON ra.rack_id = r.id
        WHERE s.bar = '$sku'
        GROUP BY s.id
    ";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $itemFound = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Item Info | Inventory System</title>
    <link rel="icon" href="img/ico/logo.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f4f4; }
        
        /* Navbar */
        .navbar { background: #000 !important; }
        .navbar-brand, .nav-link, .navbar-text { color: #fff !important; font-weight: 500; font-size: 16px; }
        .nav-link { margin-right: 15px; }
        .nav-link.active, .nav-link:focus, .nav-link:hover { color: #ffc107 !important; }
        .navbar-brand { font-size: 20px; font-weight: 700; }
        
        .info-card { background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-top: 40px; }
        .label-text { color: #6c757d; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        .value-text { font-size: 1.25rem; font-weight: 500; color: #000; }
        .price-tag { font-size: 2rem; font-weight: 700; color: #198754; }
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
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fa-solid fa-house me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="stock-tracking.php"><i class="fa-solid fa-boxes-stacked me-1"></i> Stock</a></li>
                    <li class="nav-item"><a class="nav-link" href="warehouse-layout-optimization.php"><i class="fa-solid fa-map-location-dot me-1"></i> Layout</a></li>
                    <li class="nav-item"><a class="nav-link" href="inventory-valuation.php"><i class="fa-solid fa-chart-line me-1"></i> Valuation</a></li>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="admin_users.php"><i class="fa-solid fa-users-gear me-1"></i> Users</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="backup_restore.php"><i class="fa-solid fa-database me-1"></i> Database</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item ms-lg-3"><a class="nav-link text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 120px;">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <a href="stock-tracking.php" class="btn btn-outline-dark mb-3"><i class="fa-solid fa-arrow-left me-2"></i>Back to Stock List</a>

                <?php if ($itemFound): ?>
                <div class="info-card animate__animated animate__fadeInUp">
                    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                        <div>
                            <span class="badge bg-dark mb-2"><?php echo htmlspecialchars($data['category']); ?></span>
                            <h2 class="fw-bold mb-0"><?php echo htmlspecialchars($data['item']); ?></h2>
                            <p class="text-muted mb-0"><i class="fa-solid fa-barcode me-2"></i><?php echo htmlspecialchars($data['bar']); ?></p>
                        </div>
                        <div class="text-end">
                            <div class="label-text">Unit Price</div>
                            <div class="price-tag">₱ <?php echo number_format($data['price'], 2); ?></div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <div class="label-text mb-1"><i class="fa-solid fa-boxes-stacked me-2"></i>Total Stock</div>
                                <div class="value-text"><?php echo $data['total_qty']; ?> Units</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <div class="label-text mb-1"><i class="fa-solid fa-map-location-dot me-2"></i>Locations</div>
                                <div class="value-text fs-6">
                                    <?php echo $data['loc_list'] ? htmlspecialchars($data['loc_list']) : '<span class="text-muted fst-italic">Unallocated</span>'; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if($data['total_qty'] < 10): ?>
                    <div class="alert alert-warning mt-4 d-flex align-items-center">
                        <i class="fa-solid fa-triangle-exclamation fa-2x me-3"></i>
                        <div>
                            <strong>Low Stock Warning</strong><br>
                            This item is running low. Please restock soon.
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
                <?php else: ?>
                    <div class="alert alert-danger text-center py-5 mt-4 shadow-sm border-0">
                        <i class="fa-solid fa-circle-xmark fa-3x mb-3 text-danger"></i><br>
                        <h4 class="fw-bold">Item Not Found</h4>
                        <p class="text-muted">No product found with barcode: <strong><?php echo htmlspecialchars($sku); ?></strong></p>
                        <a href="stock-tracking.php" class="btn btn-dark mt-2">Return to List</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>