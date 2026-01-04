<?php
session_start();
include "config/config.php";

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$timeout = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset(); session_destroy(); header("Location: login.php?error=timeout"); exit;
}
$_SESSION['last_activity'] = time();

$limit = 10;
if (isset($_GET["page"])) {
    $page  = $_GET["page"]; 
} else { 
    $page=1; 
};  
$start_from = ($page-1) * $limit;  
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Tracking | Inventory System</title>
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
            color: #c19802 !important; /* New gold for active/hover */
        }
        .navbar-brand { 
            font-size: 20px; 
            font-weight: 700; 
            color: #c19802 !important; /* New gold brand text */
        }
        
        /* Badge Styling */
        .badge-low { 
            background: rgba(193, 152, 2, 0.15); /* New gold with transparency */
            color: #c19802; /* New gold text */
            border: 1px solid rgba(193, 152, 2, 0.3);
        }
        .badge-ok { 
            background: rgba(2, 56, 30, 0.15); /* Dark green with transparency */
            color: #02381e; /* Dark green text */
            border: 1px solid rgba(2, 56, 30, 0.3);
        }
        .badge-out { 
            background: rgba(220, 53, 69, 0.15); /* Red with transparency */
            color: #dc3545; /* Red text */
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        /* Table Styling */
        .table-dark {
            background-color: #02381e !important; /* Dark green header */
            color: #ffffff !important; /* White text */
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(2, 56, 30, 0.05); /* Light green hover */
        }
        
        .card {
            border: 2px solid #02381e; /* Dark green border */
            background: #ffffff; /* White background */
            box-shadow: 0 4px 12px 0 rgba(2, 56, 30, 0.15); /* Dark green shadow */
        }
        
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
        
        .btn-info {
            background-color: #c19802 !important; /* New gold */
            border-color: #c19802 !important;
            color: #ffffff !important;
        }
        .btn-info:hover {
            background-color: #a17c01 !important; /* Darker gold */
            border-color: #a17c01 !important;
        }
        
        .btn-primary {
            background-color: #02381e !important; /* Dark green */
            border-color: #02381e !important;
            color: #ffffff !important;
        }
        .btn-primary:hover {
            background-color: #012916 !important; /* Darker green */
            border-color: #012916 !important;
        }
        
        .btn-danger {
            background-color: #dc3545 !important; /* Keep red for danger */
            border-color: #dc3545 !important;
            color: #ffffff !important;
        }
        
        /* Pagination Styling */
        .page-link {
            color: #02381e; /* Dark green */
            border: 1px solid rgba(2, 56, 30, 0.3);
        }
        .page-link:hover {
            color: #c19802; /* New gold on hover */
            background-color: rgba(2, 56, 30, 0.05);
            border-color: rgba(193, 152, 2, 0.3);
        }
        .page-item.active .page-link {
            background-color: #02381e !important; /* Dark green */
            border-color: #02381e !important;
            color: #ffffff !important;
        }
        
        /* Badge backgrounds */
        .badge.bg-light {
            background-color: rgba(2, 56, 30, 0.1) !important; /* Light green */
            color: #02381e !important; /* Dark green text */
            border: 1px solid rgba(2, 56, 30, 0.3) !important;
        }
        
        .badge.bg-warning {
            background-color: #c19802 !important; /* New gold */
            color: #ffffff !important; /* White text */
        }
        
        /* Logout link */
        .nav-link.text-danger {
            color: #c19802 !important; /* New gold for logout */
        }
        .nav-link.text-danger:hover {
            color: #02381e !important; /* Dark green on hover */
        }
        
        /* Headings */
        h2 {
            color: #02381e; /* Dark green */
        }
        
        /* Focus states */
        .form-control:focus {
            border-color: #c19802; /* New gold focus */
            box-shadow: 0 0 0 0.25rem rgba(193, 152, 2, 0.25); /* New gold shadow */
        }
        
        /* Container spacing */
        .container {
            margin-top: 120px;
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
                        <a class="nav-link active" href="stock-tracking.php"><i class="fa-solid fa-boxes-stacked me-1"></i> Stock</a>
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
            <h2 class="fw-bold">Stock Tracking</h2>
            <a href="add_stock.php" class="btn btn-success"><i class="fa-solid fa-plus me-1"></i> Create Product</a>
        </div>
        
        <div class="card p-3 shadow-sm border-0">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="py-3 ps-3">Item Name</th>
                        <th class="py-3">Barcode</th>
                        <th class="py-3">Category</th>
                        <th class="py-3 text-center">Total Owned</th>
                        <th class="py-3 text-center">Unallocated</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "
                        SELECT 
                            s.id, s.item, s.bar, s.category, s.stock as total_stock,
                            COALESCE(SUM(ra.quantity), 0) as allocated_stock
                        FROM stocks s
                        LEFT JOIN rack_allocations ra ON s.id = ra.stock_id
                        GROUP BY s.id
                        ORDER BY s.stock ASC
                        LIMIT $start_from, $limit
                    ";
                    $result = mysqli_query($conn, $query);

                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $total = (int)$row['total_stock']; 
                            $allocated = (int)$row['allocated_stock'];
                            $unallocated = $total - $allocated;

                            if ($total == 0) {
                                $status = '<span class="badge badge-out">Out of Stock</span>';
                            } elseif ($total < 10) {
                                $status = '<span class="badge badge-low">Low Stock</span>';
                            } else {
                                $status = '<span class="badge badge-ok">In Stock</span>';
                            }
                            
                            $unallocated_badge = ($unallocated > 0) 
                                ? "<span class='badge bg-warning text-white'>$unallocated</span>" 
                                : "<span class='text-muted'>0</span>";
                    ?>
                    <tr>
                        <td class="fw-bold ps-3"><?php echo htmlspecialchars($row['item']); ?></td>
                        <td><?php echo htmlspecialchars($row['bar']); ?></td>
                        <td><span class="badge bg-light"><?php echo htmlspecialchars($row['category']); ?></span></td>
                        <td class="text-center fw-bold fs-5"><?php echo $total; ?></td>
                        <td class="text-center"><?php echo $unallocated_badge; ?></td>
                        <td class="text-center"><?php echo $status; ?></td>
                        <td class="text-center">
                            <a href="view.php?barcode=<?php echo urlencode($row['bar']); ?>" class="btn btn-sm btn-info me-1"><i class="fa-solid fa-eye"></i></a>
                            <a href="edit_stock.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary me-1"><i class="fa-solid fa-pen"></i></a>
                            <a href="delete_stock.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?');"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center py-5 text-muted'>No products found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <?php
            $sql = "SELECT COUNT(id) FROM stocks";  
            $rs_result = mysqli_query($conn, $sql);  
            $row = mysqli_fetch_row($rs_result);  
            $total_records = $row[0];  
            $total_pages = ceil($total_records / $limit);  
            ?>
            
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php if($page <= 1){ echo 'disabled'; } ?>">
                        <a class="page-link" href="<?php if($page > 1){ echo "?page=".($page - 1); } else { echo "?>">Previous</a>
                    </li>

                    <?php for ($i=1; $i<=$total_pages; $i++): ?>
                    <li class="page-item <?php if($page == $i) { echo 'active'; } ?>">
                        <a class="page-link" href="stock-tracking.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>

                    <li class="page-item <?php if($page >= $total_pages){ echo 'disabled'; } ?>">
                        <a class="page-link" href="<?php if($page < $total_pages){ echo "?page=".($page + 1); } else { echo "?>">Next</a>
                    </li>
                </ul>
            </nav>

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