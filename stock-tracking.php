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

// --- PAGINATION LOGIC ---
$limit = 10; // Number of entries to show in a page.
if (isset($_GET["page"])) {
    $page  = $_GET["page"]; 
} else { 
    $page=1; 
};  
$start_from = ($page-1) * $limit;  
// ------------------------
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
        body { font-family: 'Inter', sans-serif; background: #f4f4f4; }
        .navbar { background: #000 !important; }
        .navbar-brand, .nav-link, .navbar-text { color: #fff !important; font-weight: 500; font-size: 16px; }
        .nav-link { margin-right: 15px; }
        .nav-link.active, .nav-link:focus, .nav-link:hover { color: #ffc107 !important; }
        .navbar-brand { font-size: 20px; font-weight: 700; }
        .badge-low { background: #ffeeba; color: #856404; }
        .badge-ok { background: #d4edda; color: #155724; }
        .badge-out { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top py-3">
        <div class="container-fluid">
            <a class="navbar-brand ps-4" href="index.php">Inventory System</a>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav me-4">
                    <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="stock-tracking.php">Stock</a></li>
                    <li class="nav-item"><a class="nav-link" href="warehouse-layout-optimization.php">Layout</a></li>
                    <li class="nav-item"><a class="nav-link" href="inventory-valuation.php">Valuation</a></li>
                    
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="admin_users.php">Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="backup_restore.php">Database</a></li>
                    <?php endif; ?>

                    <li class="nav-item ms-lg-3"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 120px;">
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
                    // Fetch data with LIMIT and OFFSET for pagination
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
                                ? "<span class='badge bg-warning text-dark'>$unallocated</span>" 
                                : "<span class='text-muted'>0</span>";
                    ?>
                    <tr>
                        <td class="fw-bold ps-3"><?php echo htmlspecialchars($row['item']); ?></td>
                        <td><?php echo htmlspecialchars($row['bar']); ?></td>
                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['category']); ?></span></td>
                        <td class="text-center fw-bold fs-5"><?php echo $total; ?></td>
                        <td class="text-center"><?php echo $unallocated_badge; ?></td>
                        <td class="text-center"><?php echo $status; ?></td>
                        <td class="text-center">
                            <a href="view.php?barcode=<?php echo urlencode($row['bar']); ?>" class="btn btn-sm btn-info me-1 text-white"><i class="fa-solid fa-eye"></i></a>
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
            // Calculate total pages
            $sql = "SELECT COUNT(id) FROM stocks";  
            $rs_result = mysqli_query($conn, $sql);  
            $row = mysqli_fetch_row($rs_result);  
            $total_records = $row[0];  
            $total_pages = ceil($total_records / $limit);  
            ?>
            
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php if($page <= 1){ echo 'disabled'; } ?>">
                        <a class="page-link" href="<?php if($page > 1){ echo "?page=".($page - 1); } else { echo "#"; } ?>">Previous</a>
                    </li>

                    <?php for ($i=1; $i<=$total_pages; $i++): ?>
                    <li class="page-item <?php if($page == $i) { echo 'active'; } ?>">
                        <a class="page-link" href="stock-tracking.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>

                    <li class="page-item <?php if($page >= $total_pages){ echo 'disabled'; } ?>">
                        <a class="page-link" href="<?php if($page < $total_pages){ echo "?page=".($page + 1); } else { echo "#"; } ?>">Next</a>
                    </li>
                </ul>
            </nav>

        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>