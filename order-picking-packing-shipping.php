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
    <title>Order Picking, Packing & Shipping | Inventory & Warehouse Management</title>
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
        .main-container {
            margin: 2em auto;
            max-width: 1000px;
            background: #fff;
            padding: 2em;
            border-radius: 8px;
            box-shadow: 0 2px 8px 0 #00000011;
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 1.1s cubic-bezier(0.23,1,0.32,1), transform 1.1s cubic-bezier(0.23,1,0.32,1);
        }
        .main-container.in-view {
            opacity: 1 !important;
            transform: none !important;
        }
        .divider {
            border-top: 1px solid #E6E6E6;
            margin: 2rem 0;
        }
        .process-step {
            margin-bottom: 2rem;
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.7s, transform 0.7s;
        }
        .process-step.in-view {
            opacity: 1;
            transform: none;
        }
        .process-icon {
            font-size: 2rem;
            color: #0d6efd;
            margin-right: 1rem;
        }
        .highlight {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.7s, transform 0.7s;
        }
        .highlight.in-view {
            opacity: 1;
            transform: none;
        }
        .example-table thead th {
            background: #363636;
            color: #fff;
            font-weight: 600;
        }
        .example-table tbody tr {
            background: #fff;
        }
        .example-table tbody tr:nth-child(even) {
            background: #f2f2f2;
        }
        .example-table tbody tr {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.7s, transform 0.7s;
        }
        .example-table tbody tr.in-view {
            opacity: 1;
            transform: none;
        }
        .alert-info.section-image-animate {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 1.1s cubic-bezier(0.23,1,0.32,1), transform 1.1s cubic-bezier(0.23,1,0.32,1);
        }
        .alert-info.section-image-animate.in-view {
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
        <!-- Section Title -->
        <div class="row mb-4">
            <div class="col-12 animate__animated animate__fadeInLeft">
                <div class="section-title mb-2">
                    <i class="fa-solid fa-truck-ramp-box me-2"></i>Order Picking, Packing & Shipping
                </div>
                <p class="lead">
                    Ensure fast, accurate, and cost-efficient delivery by optimizing your picking, packing, and shipping processes.
                </p>
            </div>
        </div>
        <hr class="divider">

        <!-- Content -->
        <div class="main-container">
            <div class="highlight d-flex align-items-center">
                <i class="fa-solid fa-bolt process-icon"></i>
                <div>
                    <strong>Tip:</strong> Use barcode scanning and digital pick lists to minimize errors and speed up order fulfillment!
                </div>
            </div>

            <h4 class="mb-3">Core Processes</h4>
            <div class="process-step d-flex align-items-start">
                <span class="process-icon"><i class="fa-solid fa-person-walking"></i></span>
                <div>
                    <strong>1. Picking:</strong> Locating and collecting items from warehouse shelves based on order details. Efficient picking reduces fulfillment times and errors.
                </div>
            </div>
            <div class="process-step d-flex align-items-start">
                <span class="process-icon"><i class="fa-solid fa-box-open"></i></span>
                <div>
                    <strong>2. Packing:</strong> Carefully packaging picked items for shipment, ensuring protection and accuracy. Use the right sized packaging and protective material.
                </div>
            </div>
            <div class="process-step d-flex align-items-start">
                <span class="process-icon"><i class="fa-solid fa-truck"></i></span>
                <div>
                    <strong>3. Shipping:</strong> Dispatching packed orders to customers using reliable carriers. Timely shipping is key to customer satisfaction.
                </div>
            </div>

            <h5 class="mb-3 mt-5">Order Fulfillment Example</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle example-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Pick Status</th>
                            <th>Packing Status</th>
                            <th>Shipping Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#1001</td>
                            <td>Jane Smith</td>
                            <td>Widget A x2, Box B x1</td>
                            <td class="text-success"><i class="fa-solid fa-check-circle"></i> Picked</td>
                            <td class="text-success"><i class="fa-solid fa-check-circle"></i> Packed</td>
                            <td class="text-warning"><i class="fa-solid fa-clock"></i> Awaiting Shipment</td>
                        </tr>
                        <tr>
                            <td>#1002</td>
                            <td>John Doe</td>
                            <td>Envelope C x3</td>
                            <td class="text-success"><i class="fa-solid fa-check-circle"></i> Picked</td>
                            <td class="text-danger"><i class="fa-solid fa-times-circle"></i> Not Packed</td>
                            <td class="text-secondary"><i class="fa-solid fa-minus-circle"></i> Pending</td>
                        </tr>
                        <tr>
                            <td>#1003</td>
                            <td>Alice Lee</td>
                            <td>Widget A x1</td>
                            <td class="text-success"><i class="fa-solid fa-check-circle"></i> Picked</td>
                            <td class="text-success"><i class="fa-solid fa-check-circle"></i> Packed</td>
                            <td class="text-success"><i class="fa-solid fa-truck"></i> Shipped</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-4 section-image-animate">
                <i class="fa-solid fa-circle-info me-2"></i>
                Streamlining picking, packing, and shipping leads to happier customers and greater warehouse efficiency!
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-dark py-5 mt-5">
        <div class="container">
            <div class="text-center">
                &copy; <?php echo date("Y"); ?> Inventory & Warehouse Management
            </div>
        </div>
    </footer>
    <!-- End Footer -->

    <!-- Bootstrap JS Bundle (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animate on scroll for .main-container, .highlight, .process-step, .example-table tbody tr, and .section-image-animate
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
        animateOnScroll('.main-container');
        animateOnScroll('.highlight');
        animateOnScroll('.process-step');
        animateOnScroll('.example-table tbody tr');
        animateOnScroll('.section-image-animate');
    </script>
</body>
</html>