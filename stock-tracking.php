<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include "config/config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Tracking | Inventory & Warehouse Management</title>
    <link rel="icon" href="img/ico/logo.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Google Fonts Inter (optional, fallback to sans-serif) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Animate.css CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        html {
            scroll-behavior: smooth;
        }
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f4f4f4;
            color: #000;
        }
        .navbar {
            background: #000 !important;
        }
        .navbar-brand, .nav-link, .navbar-text {
            color: #fff !important;
            font-weight: 500;
            font-size: 20px;
        }
        .nav-link.active, .nav-link:focus, .nav-link:hover {
            color: #ffc107 !important;
        }
        .section-title {
            font-size: 48px;
            font-weight: 600;
            letter-spacing: -0.02em;
        }
        .table thead th {
            background: #363636;
            color: #fff;
            font-weight: 600;
        }
        .table tbody tr {
            background: #fff;
        }
        .table tbody tr:nth-child(even) {
            background: #f2f2f2;
        }
        .stock-status.in-stock {
            color: #28a745;
            font-weight: 600;
        }
        .stock-status.low-stock {
            color: #ffc107;
            font-weight: 600;
        }
        .stock-status.out-stock {
            color: #dc3545;
            font-weight: 600;
        }
        .main-container {
            margin: 2em auto;
            max-width: 1100px;
            background: #fff;
            padding: 2em;
            border-radius: 8px;
            box-shadow: 0 2px 8px 0 #00000011;
        }
        .divider {
            border-top: 1px solid #E6E6E6;
            margin: 2rem 0;
        }
        /* Animation styles from index.php */
        .card-feature {
            border: 1px solid #E6E6E6;
            border-radius: 12px;
            padding: 32px 20px;
            background: #fff;
            box-shadow: 0 2px 6px 0 #00000011;
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.7s, transform 0.7s;
        }
        .card-feature.in-view {
            opacity: 1;
            transform: none;
        }
        .section-image-animate {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 1.1s cubic-bezier(0.23,1,0.32,1), transform 1.1s cubic-bezier(0.23,1,0.32,1);
        }
        .section-image-animate.in-view {
            opacity: 1 !important;
            transform: none !important;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top py-3">
        <div class="container-fluid">
            <a class="navbar-brand ps-4" href="index.php">
              <i class="fa-solid fa-warehouse me-2"></i>Inventory & Warehouse Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav gap-3">
                    <li class="nav-item ms-5">
                        <a class="nav-link" href="stock-tracking.php"><i class="fa-solid fa-cubes-stacked me-1"></i>Stock Tracking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inventory-valuation.php"><i class="fa-solid fa-coins me-1"></i>Inventory Valuation</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="warehouse-layout-optimization.php"><i class="fa-solid fa-sitemap me-1"></i>Warehouse Layout & Optimization</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="order-picking-packing-shipping.php"><i class="fa-solid fa-truck-ramp-box me-1"></i>Order Picking, Packing & Shipping</a>
                    </li>
                    <li>
                        <div class="nav-item dropdown me-3">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" onclick="markNotificationsSeen()">
                                <i class="fa-solid fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php
                                    $query = "SELECT COUNT(*) as con FROM stocks WHERE stock < 49 AND notified = 0";
                                    $result = mysqli_query($conn, $query);
                                    echo mysqli_fetch_array($result)['con'];
                                    ?>
                                    <span class="visually-hidden">unread messages</span>
                                </span>
                            </a>
                             <script>
                                function markNotificationsSeen() {
                                    fetch('assets/msee.php', {
                                        method: 'POST'
                                    }).then(() => {
                                        document.querySelector('.badge').textContent = '0';
                                    });
                                }
                            </script>
                            <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn" aria-labelledby="notificationDropdown" style="min-width: 300px;">
                                <li><h6 class="dropdown-header">Notifications</h6></li>
                                <?php
                                $quer = 'SELECT item, stock from stocks';
                                $res = mysqli_query($conn,$quer);
                                while($notif=mysqli_fetch_array($res)){
                                    if($notif['stock'] > 0 && $notif['stock'] <=49) {
                                        ?>
                                        <li><a class="dropdown-item" href="stock-tracking.php">⚠️Low stock alert: <?php echo $notif['item'];?></a></li>
                                        <?php
                                    }elseif($notif['stock'] == 0){
                                        ?>
                                        <li><a class="dropdown-item" href="stock-tracking.php">⚠️No Stock alert: <?php echo $notif['item'];?></a></li>
                                        <?php
                                    }
                                }
                                ?>
                                 <!--<hr class="dropdown-divider"></li>-->
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- End Navigation -->

    <div class="container" style="margin-top: 120px;">
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
        Item successfully deleted.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-12 animate__animated animate__fadeInLeft">
                <div class="section-title mb-2"><i class="fa-solid fa-cubes-stacked me-2"></i>Stock Tracking</div>
                <p class="lead">Monitor your inventory levels, stock movements, and status in real time.</p>
            </div>
        </div>
        <!-- Divider -->
        <hr class="divider">

        <!-- Stock Table -->
        <div class="main-container section-image-animate">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Current Stock Levels</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="fa fa-plus"></i> Add New Item
                </button>

            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th scope="col"><i class="fa-solid fa-box"></i> Item</th>
                            <th scope="col"><i class="fa-solid fa-barcode"></i> SKU</th>
                            <th scope="col"><i class="fa-solid fa-layer-group"></i> Category</th>
                            <th scope="col"><i class="fa-solid fa-warehouse"></i> Location</th>
                            <th scope="col"><i class="fa-solid fa-sort-numeric-up"></i> In Stock</th>
                            <th scope="col"><i class="fa-solid fa-bolt"></i> Status</th>
                            <th scope="col"><i class="fa-solid fa-ellipsis-h"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $quer="SELECT * FROM stocks";
                        $res=mysqli_query($conn,$quer);
                        while($data = mysqli_fetch_array($res)){
                            ?>
                            <tr>
                                <td><?php echo $data['item']?></td>
                                <td><?php echo $data['bar']?></td>
                                <td><?php echo $data['category']?></td>
                                <td><?php echo $data['location']?></td>
                                <td><?php echo $data['stock']?></td>
                                <td><?php
                                if($data['stock'] == 0){
                                    echo '<span class="stock-status out-stock"><i class="fa-solid fa-xmark-circle"></i> Out of Stock</span>';
                                }elseif($data['stock'] > 50){
                                    echo '<span class="stock-status in-stock"><i class="fa-solid fa-circle"></i> In Stock</span>';
                                }else{
                                    echo '<span class="stock-status low-stock"><i class="fa-solid fa-triangle-exclamation"></i> Low Stock</span>';
                                }
                                ?></td>
                                <td>
                                <a href="view.php?barcode=<?php echo $data['bar'];?>" class="btn btn-sm btn-outline-info"><i class="fa fa-eye"></i></a>
                                <a href="edit.php?barcode=<?php echo $data['bar'];?>" class="btn btn-sm btn-outline-warning"><i class="fa fa-edit"></i></a>
                                <a href="delete.php?barcode=<?php echo $data['bar']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this item?');"><i class="fa fa-trash"></i></a>

                            </td>
                            </tr>
                            <?php
                        } 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-dark py-5 mt-5">
        <div class="container">
            <div class="text-center">
                &copy; <?php echo date("Y"); ?>  Inventory & Warehouse Management
            </div>
        </div>
    </footer>
    <!-- End Footer -->

    <!-- Bootstrap JS Bundle (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animate on scroll for .card-feature and .section-image-animate
        function animateOnScroll(selector, className = 'in-view') {
            const elements = document.querySelectorAll(selector);
            function check() {
                const triggerBottom = window.innerHeight * 0.92;
                let delay = 0;
                elements.forEach((el, idx) => {
                    const rect = el.getBoundingClientRect();
                    if(rect.top < triggerBottom && !el.classList.contains(className)) {
                        el.style.transitionDelay = (delay * 0.13) + "s";
                        el.classList.add(className);
                        delay++;
                    }
                });
            }
            window.addEventListener('scroll', check);
            document.addEventListener('DOMContentLoaded', check);
        }
        animateOnScroll('.card-feature');
        animateOnScroll('.section-image-animate');
    </script>
    <!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="assets/stockadd.php">
        <div class="modal-header">
          <h5 class="modal-title" id="addItemModalLabel">Add New Stock Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="item" class="form-label">Item Name</label>
            <input type="text" class="form-control" name="item" required>
          </div>
          <div class="mb-3">
            <label for="sku" class="form-label">SKU</label>
            <input type="text" class="form-control" name="sku" required>
          </div>
          <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <input type="text" class="form-control" name="category" required>
          </div>
          <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" class="form-control" name="location" required>
          </div>
          <div class="mb-3">
            <label for="stock" class="form-label">Stock Quantity</label>
            <input type="number" class="form-control" name="stock" required>
          </div>
          <div class="mb-3">
            <label for="price" class="form-label">Price</label>
            <input type="text" class="form-control" name="price" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="addItem" class="btn btn-success">Add Item</button>
        </div>
      </form>
    </div>
  </div>
</div>

</body>
</html>