<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include "config/config.php";
?>
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
    <title>Inventory Valuation | Inventory & Warehouse Management</title>
    <link rel="icon" href="img/ico/logo.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- Google Fonts Inter (optional, fallback to sans-serif) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Animate.css CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        html {
            scroll-behavior: smooth;
            height: 100%;
        }
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f4f4f4;
            color: #000;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
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
        .valuation-method {
            font-size: 20px;
            font-weight: 500;
        }
        .valuation-result {
            font-size: 24px;
            font-weight: 700;
            color: #1a7f37;
        }
        .table thead th {
            background: #363636;
            color: #fff;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .table tbody tr {
            background: #fff;
        }
        .table tbody tr:nth-child(even) {
            background: #f2f2f2;
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

        /* Scrollable table container */
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
            display: block;
        }

        /* Footer styling */
        footer {
            background: #fff;
            padding: 1rem 0;
            margin-top: auto;
        }

        /* Pagination styling */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.75rem;
            border: 1px solid transparent;
            border-radius: 4px;
            margin-left: 2px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #0d6efd;
            color: white !important;
            border: 1px solid #0d6efd;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e9ecef;
            border: 1px solid #dee2e6;
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
                        <a class="nav-link" href="order-fulfillment.php"><i class="fa-solid fa-truck-ramp-box me-1"></i>Order Fulfillment</a>
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

    <div class="container text-center flex-grow-1" style="margin-top: 120px; padding-bottom: 2rem;">
        <!-- Section Title -->
        <div class="row mb-4">
            <div class="col-12 animate__animated animate__fadeInLeft">
                <div class="section-title mb-2">
                    <i class="fa-solid fa-coins me-2"></i>Inventory Valuation
                </div>
                <p class="lead">
                    The current Inventory Valuation
                </p>
            </div>
        </div>
        <!-- Divider -->
        <hr class="divider">

        <!-- Inventory Valuation Table -->
        <div class="main-container section-image-animate">
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle" id="valuationTable">
                    <thead>
                        <tr>
                            <th scope="col"><i class="fa-solid fa-box"></i> Item</th>
                            <th scope="col"><i class="fa-solid fa-layer-group"></i> Category</th>
                            <th scope="col"><i class="fa-solid fa-warehouse"></i> Location</th>
                            <th scope="col"><i class="fa-solid fa-sort-numeric-up"></i> In Stock</th>
                            <th scope="col"><i class="fa-solid fa-money-bill-wave"></i> Unit Cost</th>
                            <th scope="col"><i class="fa-solid fa-calculator"></i> Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $query = "SELECT *, (price * stock) as totalCost FROM stocks";
                            $res = mysqli_query($conn, $query);
                            while ($data = mysqli_fetch_array($res)) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data['item']); ?></td>
                            <td><?php echo htmlspecialchars($data['category']); ?></td> 
                            <td><?php echo htmlspecialchars($data['location']); ?></td> 
                            <td><?php echo (int)$data['stock']; ?></td>
                            <td><?php echo number_format($data['price'], 2); ?></td>
                            <td><?php echo number_format($data['totalCost'], 2); ?></td>
                        </tr>
                        <?php
                            }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Total Inventory Value</th>
                            <?php
                                $query = "SELECT SUM(price * stock) as totalval FROM stocks";
                                $res = mysqli_query($conn, $query);
                                while($data2 = mysqli_fetch_array($res)){
                            ?>
                            <th><?php echo number_format($data2['totalval'], 2);?></th>
                            <?php
                                }
                            ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light py-3">
        <div class="container text-center">
            &copy; <?= date("Y") ?> Xophia’s Inventory & Warehouse Management
        </div>
    </footer>
    <!-- End Footer -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS Bundle (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Initialize DataTable with pagination
        $(document).ready(function() {
            $('#valuationTable').DataTable({
                responsive: true,
                paging: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search items...",
                    lengthMenu: "Show _MENU_ entries",
                    paginate: {
                        previous: '<i class="fa-solid fa-chevron-left"></i>',
                        next: '<i class="fa-solid fa-chevron-right"></i>'
                    }
                },
                initComplete: function() {
                    // Remove dropdown arrow from length menu
                    $('.dataTables_length select').removeClass('form-select-sm').addClass('form-select');
                    $('.dataTables_filter input').removeClass('form-control-sm').addClass('form-control');
                }
            });
        });

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
</body>
</html>