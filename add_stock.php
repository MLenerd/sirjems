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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item = mysqli_real_escape_string($conn, $_POST['item']);
    $bar = mysqli_real_escape_string($conn, $_POST['bar']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $stock = intval($_POST['stock']); // Master Stock Quantity
    $price = floatval($_POST['price']);

    // Check duplicate barcode
    $check = mysqli_query($conn, "SELECT id FROM stocks WHERE bar = '$bar'");
    if(mysqli_num_rows($check) > 0) {
        $error = "Barcode '$bar' already exists!";
    } else {
        // Insert with Initial Stock
        $sql = "INSERT INTO stocks (item, bar, category, price, stock) VALUES ('$item', '$bar', '$category', '$price', '$stock')";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: stock-tracking.php");
            exit;
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Product | Inventory System</title>
    <link rel="icon" href="img/ico/logo.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f4f4; }
        .navbar { background: #000 !important; }
        .navbar-brand, .nav-link, .navbar-text { color: #fff !important; font-weight: 500; font-size: 16px; }
        .nav-link { margin-right: 15px; }
        .nav-link.active, .nav-link:focus, .nav-link:hover { color: #ffc107 !important; }
        .navbar-brand { font-size: 20px; font-weight: 700; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top py-3">
        <div class="container-fluid">
            <a class="navbar-brand ps-4" href="index.php"><i class="fa-solid fa-warehouse me-2"></i>Inventory System</a>
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

    <div class="container" style="margin-top: 120px; max-width: 600px;">
        <div class="card p-4 shadow-sm border-0">
            <h3 class="mb-4 fw-bold">Create New Product</h3>
            <?php if(isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Item Name</label>
                    <input type="text" name="item" class="form-control" required placeholder="e.g. Century Tuna">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Barcode (PG)</label>
                    <input type="text" name="bar" class="form-control" required placeholder="Scan or type barcode">
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label fw-bold">Category</label>
                        <input type="text" name="category" class="form-control" required placeholder="e.g. Canned Goods">
                    </div>
                    <div class="col">
                        <label class="form-label fw-bold">Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" required placeholder="0.00">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Total Stock Quantity</label>
                    <input type="number" name="stock" class="form-control" required placeholder="How many do you have?">
                    <div class="form-text">This is your total inventory. You can assign it to racks later.</div>
                </div>

                <button type="submit" class="btn btn-success w-100 py-2">Save Product</button>
                <a href="stock-tracking.php" class="btn btn-light w-100 mt-2">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>