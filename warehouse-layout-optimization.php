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

// --- HANDLE ACTIONS ---

// 1. CREATE RACK
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_rack') {
    $rack_name = mysqli_real_escape_string($conn, $_POST['rack_name']);
    $check = mysqli_query($conn, "SELECT id FROM racks WHERE name = '$rack_name'");
    if(mysqli_num_rows($check) > 0) {
        $error_msg = "Rack <b>$rack_name</b> already exists!";
    } else {
        if(mysqli_query($conn, "INSERT INTO racks (name) VALUES ('$rack_name')")) {
            $success_msg = "Rack <b>$rack_name</b> created.";
        } else {
            $error_msg = "Error: " . mysqli_error($conn);
        }
    }
}

// 2. ALLOCATE ITEM (Validation Logic)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_allocation') {
    $rack_id = intval($_POST['rack_id']);
    $stock_id = intval($_POST['stock_id']);
    $qty = intval($_POST['quantity']);

    // Check Availability
    $checkQ = mysqli_query($conn, "
        SELECT s.stock as total, COALESCE(SUM(ra.quantity), 0) as allocated 
        FROM stocks s 
        LEFT JOIN rack_allocations ra ON s.id = ra.stock_id 
        WHERE s.id = $stock_id
    ");
    $row = mysqli_fetch_assoc($checkQ);
    $available = $row['total'] - $row['allocated'];

    if ($qty > $available) {
        $error_msg = "Cannot add $qty units. Only <b>$available</b> units are unallocated.";
    } else {
        // Proceed with allocation
        $checkExist = mysqli_query($conn, "SELECT id, quantity FROM rack_allocations WHERE rack_id = $rack_id AND stock_id = $stock_id");
        if (mysqli_num_rows($checkExist) > 0) {
            $existRow = mysqli_fetch_assoc($checkExist);
            $newQty = $existRow['quantity'] + $qty;
            mysqli_query($conn, "UPDATE rack_allocations SET quantity = $newQty WHERE id = " . $existRow['id']);
        } else {
            mysqli_query($conn, "INSERT INTO rack_allocations (rack_id, stock_id, quantity) VALUES ($rack_id, $stock_id, $qty)");
        }
        $success_msg = "Item successfully added to rack.";
    }
}

// 3. REMOVE ALLOCATION
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'remove_allocation') {
    $alloc_id = intval($_POST['alloc_id']);
    mysqli_query($conn, "DELETE FROM rack_allocations WHERE id = $alloc_id");
    $success_msg = "Item removed from rack (Returned to unallocated stock).";
}

// 4. DELETE RACK
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_rack') {
    $rack_id = intval($_POST['rack_id']);
    $check = mysqli_query($conn, "SELECT id FROM rack_allocations WHERE rack_id = $rack_id");
    if(mysqli_num_rows($check) > 0) {
        $error_msg = "Cannot delete rack. Remove items first.";
    } else {
        mysqli_query($conn, "DELETE FROM racks WHERE id = $rack_id");
        $success_msg = "Rack deleted.";
    }
}

// --- FETCH PRODUCTS & AVAILABILITY ---
$products = [];
$pQ = "
    SELECT s.id, s.item, (s.stock - COALESCE(SUM(ra.quantity), 0)) as available 
    FROM stocks s 
    LEFT JOIN rack_allocations ra ON s.id = ra.stock_id 
    GROUP BY s.id 
    HAVING available > 0 
    ORDER BY s.item ASC
";
$pR = mysqli_query($conn, $pQ);
while($row = mysqli_fetch_assoc($pR)) { $products[] = $row; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Layout Optimization | Inventory System</title>
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
        .rack-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px; min-height: 250px; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .rack-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); border-color: #000; }
        .rack-header { font-size: 1.2rem; font-weight: 700; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .item-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 0.95rem; }
        .qty-badge { background: #eee; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 0.85rem; }
        .btn-trash { color: #dc3545; cursor: pointer; opacity: 0.3; transition:0.2s; border:none; background:none; padding:0; }
        .btn-trash:hover { opacity: 1; }
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
                        <a class="nav-link active" href="index.php"><i class="fa-solid fa-house me-1"></i> Dashboard</a>
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

    <div class="container" style="margin-top: 120px;">
        
        <?php if(isset($success_msg)) echo "<div class='alert alert-success'>$success_msg</div>"; ?>
        <?php if(isset($error_msg)) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Warehouse Layout</h2>
            <div>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createRackModal">
                    <i class="fa-solid fa-plus me-1"></i> New Rack
                </button>
            </div>
        </div>
        
        <div class="row g-4">
            <?php
            $racksQ = "SELECT * FROM racks ORDER BY name ASC";
            $racksR = mysqli_query($conn, $racksQ);

            if(mysqli_num_rows($racksR) > 0) {
                while($rack = mysqli_fetch_assoc($racksR)) {
                    $rid = $rack['id'];
                    $rname = $rack['name'];
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="rack-card">
                        <div class="rack-header">
                            <div><i class="fa-solid fa-map-pin me-2 text-danger"></i> <?php echo htmlspecialchars($rname); ?></div>
                            <div class="d-flex gap-2">
                                <form method="POST" onsubmit="return confirm('Delete this rack?');">
                                    <input type="hidden" name="action" value="delete_rack">
                                    <input type="hidden" name="rack_id" value="<?php echo $rid; ?>">
                                    <button class="btn btn-sm btn-outline-danger border-0"><i class="fa-solid fa-trash"></i></button>
                                </form>
                                <button class="btn btn-dark btn-sm rounded-pill" 
                                        data-bs-toggle="modal" data-bs-target="#allocateModal" 
                                        data-rackid="<?php echo $rid; ?>" data-rackname="<?php echo htmlspecialchars($rname); ?>">
                                    <i class="fa-solid fa-plus"></i> Add
                                </button>
                            </div>
                        </div>

                        <div class="rack-contents">
                            <?php
                            $allocQ = "SELECT ra.id as alloc_id, ra.quantity, s.item 
                                       FROM rack_allocations ra 
                                       JOIN stocks s ON ra.stock_id = s.id 
                                       WHERE ra.rack_id = $rid";
                            $allocR = mysqli_query($conn, $allocQ);

                            if(mysqli_num_rows($allocR) > 0) {
                                while($item = mysqli_fetch_assoc($allocR)) {
                            ?>
                                <div class="item-row">
                                    <span class="fw-bold text-secondary"><?php echo htmlspecialchars($item['item']); ?></span>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="qty-badge"><?php echo $item['quantity']; ?></span>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="action" value="remove_allocation">
                                            <input type="hidden" name="alloc_id" value="<?php echo $item['alloc_id']; ?>">
                                            <button class="btn-trash"><i class="fa-solid fa-xmark"></i></button>
                                        </form>
                                    </div>
                                </div>
                            <?php 
                                }
                            } else {
                                echo "<div class='text-center text-muted py-5 small'>Empty Rack</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<div class='col-12 text-center py-5 text-muted'>No racks found. Create one!</div>";
            }
            ?>
        </div>
    </div>

    <div class="modal fade" id="createRackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header fw-bold">Create New Rack</div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_rack">
                        <label class="form-label">Rack Name</label>
                        <input type="text" class="form-control" name="rack_name" required placeholder="e.g. A-101">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success w-100">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="allocateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header fw-bold">Add Item to <span id="displayRackName" class="ms-1"></span></div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_allocation">
                        <input type="hidden" name="rack_id" id="modalRackId">
                        
                        <div class="mb-3">
                            <label class="form-label">Select Product</label>
                            <select name="stock_id" class="form-select" id="productSelect" onchange="updateMax()" required>
                                <option value="" disabled selected>-- Choose Product (Available Qty) --</option>
                                <?php foreach($products as $p): ?>
                                    <option value="<?php echo $p['id']; ?>" data-max="<?php echo $p['available']; ?>">
                                        <?php echo htmlspecialchars($p['item']); ?> (Avail: <?php echo $p['available']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" id="qtyInput" class="form-control" value="1" min="1" required>
                            <div class="form-text text-end fw-bold text-success" id="maxText"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-dark w-100">Add to Rack</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var allocateModal = document.getElementById('allocateModal');
        allocateModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var rackId = button.getAttribute('data-rackid');
            var rackName = button.getAttribute('data-rackname');
            allocateModal.querySelector('#modalRackId').value = rackId;
            allocateModal.querySelector('#displayRackName').textContent = rackName;
            document.getElementById('productSelect').value = "";
            document.getElementById('maxText').textContent = "";
        });

        function updateMax() {
            var select = document.getElementById('productSelect');
            var selectedOption = select.options[select.selectedIndex];
            var max = selectedOption.getAttribute('data-max');
            
            if (max) {
                document.getElementById('qtyInput').max = max;
                document.getElementById('maxText').textContent = "Max Available: " + max;
            }
        }
    </script>
</body>
</html>