<?php
session_start();
include "config/config.php";

// --- SECURITY BLOCK ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?error=timeout");
    exit;
}
$_SESSION['last_activity'] = time();
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 300) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
// ----------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Valuation | Inventory System</title>
    <link rel="icon" href="img/ico/logo.ico"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: #ffffff; /* White background */
            color: #02381e; /* Dark green text */
        }
        
        /* Navbar Styling */
        .navbar { 
            background: #02381e !important; /* Dark green navbar */
        }
        .navbar-brand, .nav-link, .navbar-text { 
            color: #ffffff !important; /* White text */
            font-weight: 500; 
            font-size: 16px; 
        }
        .nav-link { margin-right: 15px; }
        .nav-link.active, .nav-link:focus, .nav-link:hover { 
            color: #c19802 !important; /* Gold for active/hover */
        }
        .navbar-brand { 
            font-size: 20px; 
            font-weight: 700; 
            color: #c19802 !important; /* Gold brand text */
        }
        
        /* Valuation Card */
        .card-total { 
            background: #02381e; /* Dark green background */
            color: #ffffff; /* White text */
            border-radius: 12px; 
            padding: 30px; 
            margin-bottom: 30px; 
            border: 2px solid #02381e;
            box-shadow: 0 4px 12px rgba(2, 56, 30, 0.15); /* Dark green shadow */
        }
        
        /* Table Container */
        .table-container { 
            background: #ffffff; /* White background */
            border-radius: 12px; 
            padding: 20px; 
            box-shadow: 0 4px 12px rgba(2, 56, 30, 0.15); /* Dark green shadow */
            border: 2px solid #02381e; /* Dark green border */
        }
        
        /* Page Title */
        .page-title { 
            font-weight: 700; 
            font-size: 32px; 
            color: #02381e; /* Dark green */
        }
        
        /* Table Styling */
        .table-light {
            background-color: #02381e !important; /* Dark green header */
            color: #ffffff !important; /* White text */
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(2, 56, 30, 0.05); /* Light green hover */
        }
        
        /* Button Styling */
        .btn-dark {
            background-color: #02381e !important; /* Dark green */
            border-color: #02381e !important;
            color: #ffffff !important;
        }
        .btn-dark:hover {
            background-color: #012916 !important; /* Darker green */
            border-color: #012916 !important;
        }
        
        /* Badge Styling */
        .badge.bg-light {
            background-color: rgba(2, 56, 30, 0.1) !important; /* Light green */
            color: #02381e !important; /* Dark green text */
            border: 1px solid rgba(2, 56, 30, 0.3) !important;
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
        
        /* Money amounts color */
        .fw-bold {
            color: #02381e; /* Dark green for bold numbers */
        }
        
        /* Total value highlight */
        .display-4 {
            color: #ffffff; /* White for total amount */
        }
        
        /* Subtitle in total card */
        .text-white-50 {
            color: rgba(255, 255, 255, 0.85) !important;
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
                        <a class="nav-link active" href="inventory-valuation.php"><i class="fa-solid fa-chart-line me-1"></i> Valuation</a>
                    </li>

                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_users.php"><i class="fa-solid fa-users-gear me-1"></i> Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="backup_restore.php"><i class="fa-solid fa-database me-1"></i> Database</a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item ms-lg-3">
                        <a class="nav-link text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
                    </li>
                    
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="page-title mb-0">Inventory Valuation</div>
            <button class="btn btn-dark btn-sm" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print Report</button>
        </div>
        
        <?php
        // Updated logic: We sum up allocations first, then multiply by price
        $totalQuery = "
            SELECT SUM(s.price * COALESCE(alloc_sum.total_qty, 0)) as grand_total 
            FROM stocks s
            LEFT JOIN (
                SELECT stock_id, SUM(quantity) as total_qty 
                FROM rack_allocations 
                GROUP BY stock_id
            ) alloc_sum ON s.id = alloc_sum.stock_id
        ";
        $totalResult = mysqli_query($conn, $totalQuery);
        $totalRow = mysqli_fetch_assoc($totalResult);
        $grandTotal = $totalRow['grand_total'] ? $totalRow['grand_total'] : 0;
        ?>

        <div class="card-total text-center">
            <h4 class="text-white-50">Total Warehouse Asset Value</h4>
            <h1 class="display-4 fw-bold">₱ <?php echo number_format($grandTotal, 2); ?></h1>
        </div>
        
        <div class="table-container">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 ps-3">Item</th>
                        <th class="py-3">Category</th>
                        <th class="py-3 text-end">Unit Price</th>
                        <th class="py-3 text-center">Qty (Allocated)</th>
                        <th class="py-3 text-end pe-3">Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $listQuery = "
                        SELECT 
                            s.item, s.category, s.price,
                            COALESCE(SUM(ra.quantity), 0) as total_stock
                        FROM stocks s
                        LEFT JOIN rack_allocations ra ON s.id = ra.stock_id
                        GROUP BY s.id
                        ORDER BY total_stock DESC
                    ";
                    $listResult = mysqli_query($conn, $listQuery);
                    
                    if(mysqli_num_rows($listResult) > 0) {
                        while($row = mysqli_fetch_assoc($listResult)) {
                            $qty = $row['total_stock'];
                            $rowTotal = $row['price'] * $qty;
                    ?>
                    <tr>
                        <td class="fw-bold ps-3"><?php echo htmlspecialchars($row['item']); ?></td>
                        <td><span class="badge bg-light"><?php echo htmlspecialchars($row['category']); ?></span></td>
                        <td class="text-end">₱ <?php echo number_format($row['price'], 2); ?></td>
                        <td class="text-center fw-bold"><?php echo $qty; ?></td>
                        <td class="text-end fw-bold pe-3">₱ <?php echo number_format($rowTotal, 2); ?></td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center py-5 text-muted'>No items found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const timeoutDuration = <?php echo $timeout_duration; ?>;
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