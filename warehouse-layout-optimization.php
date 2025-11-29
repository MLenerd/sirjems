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
    <title>Warehouse Layout & Optimization | Inventory & Warehouse Management</title>
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
        .tip-icon {
            color: #007bff;
            margin-right: 0.5rem;
        }
        .layout-img {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 1px 5px #0001;
            margin-bottom: 1rem;
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 1.1s cubic-bezier(0.23,1,0.32,1), transform 1.1s cubic-bezier(0.23,1,0.32,1);
        }
        .layout-img.in-view {
            opacity: 1 !important;
            transform: none !important;
        }
        .opt-list li {
            margin-bottom: 1rem;
            font-size: 1.1rem;
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.7s, transform 0.7s;
        }
        .opt-list li.in-view {
            opacity: 1;
            transform: none;
        }
        .highlight {
            background: #e7f5ff;
            border-left: 5px solid #0d6efd;
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
        
        /* Warehouse map container */
        .warehouse-map-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .map-title {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
            font-weight: 600;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        /* Enhanced warehouse grid */
        .warehouse-map {
            display: grid;
            grid-template-columns: repeat(14, 1fr);
            grid-template-rows: repeat(12, 1fr);
            gap: 4px; /* Slimmer aisles */
            height: 650px;
            margin: 0 auto;
            max-width: 1000px;
        }
        
        .map-cell {
            background: #e9ecef;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
            font-size: 12px;
            text-align: center;
            overflow: hidden;
            border: 1px solid #ced4da;
        }
        
        .aisle {
            background: #f0f0f0;
            border: 1px dashed #bbb; /* Slimmer aisle style */
        }
        
        .unloading-dock {
            background: linear-gradient(135deg, #4CAF50, #8BC34A);
            grid-column: span 3;
            grid-row: span 2;
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }
        
        .loading-dock {
            background: linear-gradient(135deg, #FF9800, #FFC107);
            grid-column: span 3;
            grid-row: span 2;
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }
        
        .shelf {
            color: white;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        /* New category-based shelf coloring */
        .shelf-electronics {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }
        
        .shelf-tools-hardware {
            background: linear-gradient(135deg, #5a67d8, #9f7aea);
        }
        
        .shelf-home-appliances {
            background: linear-gradient(135deg, #ff6b6b, #ff9a9e);
        }
        
        .shelf-furniture {
            background: linear-gradient(135deg, #48bb78, #81e6d9);
        }
        
        .shelf-health-personal {
            background: linear-gradient(135deg, #ed64a6, #fbb6ce);
        }
        
        .shelf-cleaning-supplies {
            background: linear-gradient(135deg, #a0aec0, #cbd5e0);
        }
        
        .shelf-packaging-shipping {
            background: linear-gradient(135deg, #f6ad55, #fbd38d);
        }
        
        .shelf-office-stationery {
            background: linear-gradient(135deg, #4299e1, #90cdf4);
        }
        
        .shelf-food-beverages {
            background: linear-gradient(135deg, #9f7aea, #d6bcfa);
        }
        
        .shelf-automotive-parts {
            background: linear-gradient(135deg, #e53e3e, #fc8181);
        }
        
        .shelf-clothing-apparel {
            background: linear-gradient(135deg, #dd6b20, #f6ad55);
        }
        
        .shelf-footwear {
            background: linear-gradient(135deg, #b7791f, #ecc94b);
        }
        
        .shelf-toys-games {
            background: linear-gradient(135deg, #38a169, #68d391);
        }
        
        .shelf-sports-fitness {
            background: linear-gradient(135deg, #0987a0, #00b5d8);
        }
        
        .shelf-books-media {
            background: linear-gradient(135deg, #805ad5, #b794f4);
        }
        
        .shelf-industrial-equipment {
            background: linear-gradient(135deg, #4a5568, #718096);
        }
        
        .shelf-electrical-components {
            background: linear-gradient(135deg, #2b6cb0, #63b3ed);
        }
        
        .shelf-plumbing-supplies {
            background: linear-gradient(135deg, #319795, #4fd1c5);
        }
        
        .shelf-garden-outdoor {
            background: linear-gradient(135deg, #276749, #48bb78);
        }
        
        .shelf-safety-security {
            background: linear-gradient(135deg, #9c4221, #dd6b20);
        }
        
        .shelf:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(79, 172, 254, 0.7);
            z-index: 10;
        }
        
        .shelf-tooltip {
            position: absolute;
            background: rgba(30, 30, 46, 0.95);
            color: white;
            padding: 15px;
            border-radius: 10px;
            width: 300px;
            z-index: 100;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            display: none;
            border: 1px solid #4dabf7;
            top: calc(100% + 10px);
            left: 50%;
            transform: translateX(-50%);
        }
        
        .shelf-tooltip h3 {
            color: #4facfe;
            margin-bottom: 10px;
            font-size: 1.2rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 8px;
        }
        
        .shelf-items {
            /* Removed scroll feature as requested */
        }
        
        .item {
            display: flex;
            justify-content: space-between;
            padding: 8px 5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .item:last-child {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: 500;
            flex: 2;
        }
        
        .item-category {
            background: rgba(121, 134, 203, 0.2);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin: 0 5px;
        }
        
        .item-qty {
            background: rgba(79, 172, 254, 0.2);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            flex: 1;
            text-align: center;
        }
        
        .legend {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            padding: 5px 10px;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }
        
        .stats-panel {
            background: #e9f7fe;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        .tip-card {
            background: #fff9db;
            border-left: 4px solid #ffd43b;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .category-tag {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 5px;
        }
        
        @media (max-width: 768px) {
            .warehouse-map {
                grid-template-columns: repeat(8, 1fr);
                grid-template-rows: repeat(18, 1fr);
                height: 800px;
            }
            
            .shelf-tooltip {
                width: 250px;
                font-size: 0.9rem;
            }
            
            .legend {
                flex-direction: column;
                align-items: flex-start;
            }
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
                        <a class="nav-link active" href="warehouse-layout-optimization.php"><i class="fa-solid fa-sitemap me-1"></i>Warehouse Layout & Optimization</a>
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
                    <i class="fa-solid fa-sitemap me-2"></i>Warehouse Layout & Optimization
                </div>
                <p class="lead">
                    Maximize your warehouse space and efficiency with our optimized layout. Items are grouped by category for faster picking.
                </p>
            </div>
        </div>
        <hr class="divider">
        
        <!-- Warehouse Map -->
        <div class="warehouse-map-container animate__animated animate__fadeInUp">
            <h3 class="map-title">Interactive Warehouse Layout</h3>
            <div class="warehouse-map" id="warehouseMap">
                <!-- Map cells will be generated by JavaScript -->
            </div>
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #4facfe, #00f2fe);"></div>
                    <span>Electronics</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #5a67d8, #9f7aea);"></div>
                    <span>Tools & Hardware</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #ff6b6b, #ff9a9e);"></div>
                    <span>Home Appliances</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #48bb78, #81e6d9);"></div>
                    <span>Furniture</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #ed64a6, #fbb6ce);"></div>
                    <span>Health & Personal Care</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #a0aec0, #cbd5e0);"></div>
                    <span>Cleaning & Janitorial</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #f6ad55, #fbd38d);"></div>
                    <span>Packaging & Shipping</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #4299e1, #90cdf4);"></div>
                    <span>Office & Stationery</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #9f7aea, #d6bcfa);"></div>
                    <span>Food & Beverages</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #e53e3e, #fc8181);"></div>
                    <span>Automotive Parts</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #dd6b20, #f6ad55);"></div>
                    <span>Clothing & Apparel</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #b7791f, #ecc94b);"></div>
                    <span>Footwear</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #38a169, #68d391);"></div>
                    <span>Toys & Games</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #0987a0, #00b5d8);"></div>
                    <span>Sports & Fitness</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #805ad5, #b794f4);"></div>
                    <span>Books & Media</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #4a5568, #718096);"></div>
                    <span>Industrial Equipment</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #2b6cb0, #63b3ed);"></div>
                    <span>Electrical Components</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #319795, #4fd1c5);"></div>
                    <span>Plumbing Supplies</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #276749, #48bb78);"></div>
                    <span>Garden & Outdoor</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #9c4221, #dd6b20);"></div>
                    <span>Safety & Security</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #f0f0f0; border: 1px dashed #bbb;"></div>
                    <span>Aisle</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #4CAF50, #8BC34A);"></div>
                    <span>Unloading Dock</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #FF9800, #FFC107);"></div>
                    <span>Loading Dock</span>
                </div>
            </div>
        </div>
        
        <?php
        // Get all items from the database
        $itemsQuery = "SELECT * FROM stocks";
        $itemsResult = mysqli_query($conn, $itemsQuery);
        $allItems = [];
        $totalItems = 0;
        $totalValue = 0;
        
        while($item = mysqli_fetch_assoc($itemsResult)) {
            $allItems[] = $item;
            $totalItems += $item['stock'];
            $totalValue += $item['stock'] * $item['price'];
        }
        
        // Group items by location (rack)
        $itemsByLocation = [];
        foreach($allItems as $item) {
            $location = $item['location'];
            if(!isset($itemsByLocation[$location])) {
                $itemsByLocation[$location] = [];
            }
            $itemsByLocation[$location][] = $item;
        }
        
        // Get the number of locations
        $locationCount = count($itemsByLocation);
        ?>
        
        <!-- Stats Panel -->
        <div class="stats-panel">
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                    <div class="stat-value"><?php echo number_format($totalItems); ?></div>
                    <div class="stat-label">Total Items in Stock</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-warehouse fa-2x text-success mb-2"></i>
                    <div class="stat-value"><?php echo $locationCount; ?></div>
                    <div class="stat-label">Active Racks</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-coins fa-2x text-warning mb-2"></i>
                    <div class="stat-value">₱<?php echo number_format($totalValue, 2); ?></div>
                    <div class="stat-label">Total Inventory Value</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock fa-2x text-info mb-2"></i>
                    <div class="stat-value">17 min</div>
                    <div class="stat-label">Avg. Picking Time</div>
                </div>
            </div>
        </div>
        
        <!-- Optimization Tips -->
        <div class="tip-card">
            <h5><i class="fas fa-lightbulb text-warning me-2"></i>Optimization Tip</h5>
            <p>Our layout groups items by category to minimize travel time. We've implemented 20 distinct categories with unique color coding for easy visual identification.</p>
        </div>

        <!-- Content -->
        <div class="main-container">

            <div class="highlight d-flex align-items-center">
                <i class="fa-solid fa-lightbulb tip-icon fa-2x"></i>
                <div>
                    <strong>Tip:</strong> Organize your warehouse by grouping fast-moving items near packing and shipping areas to speed up order fulfillment!
                </div>
            </div>

            <h4 class="mb-3">Best Practices for Warehouse Layout & Optimization</h4>
            <ul class="opt-list">
                <li><i class="fa-solid fa-arrow-right text-primary me-2"></i><strong>Define clear zones:</strong> Separate receiving, storage, picking, packing, and shipping areas for smooth workflow.</li>
                <li><i class="fa-solid fa-arrow-right text-primary me-2"></i><strong>Utilize vertical space:</strong> Install tall shelving and racks to maximize cubic storage.</li>
                <li><i class="fa-solid fa-arrow-right text-primary me-2"></i><strong>Use logical item placement:</strong> Place high-turnover or heavy items closer to dispatch to reduce travel time and effort.</li>
                <li><i class="fa-solid fa-arrow-right text-primary me-2"></i><strong>Label everything:</strong> Clear, consistent labeling minimizes picking errors and confusion.</li>
                <li><i class="fa-solid fa-arrow-right text-primary me-2"></i><strong>Invest in technology:</strong> Use barcode scanners, WMS (Warehouse Management System), and digital maps for real-time optimization.</li>
                <li><i class="fa-solid fa-arrow-right text-primary me-2"></i><strong>Keep aisles wide and clear:</strong> Ensure safe and efficient movement of people and equipment.</li>
                <li><i class="fa-solid fa-arrow-right text-primary me-2"></i><strong>Review and improve:</strong> Regularly audit your layout and flow for bottlenecks and adjust as needed.</li>
            </ul>

            <div class="alert alert-info mt-4 section-image-animate">
                <i class="fa-solid fa-circle-info me-2"></i>
                An optimized warehouse layout not only saves time but also reduces costs and improves safety!
            </div>
        </div>
    </div>

    <!-- Shelf Tooltip -->
    <div class="shelf-tooltip" id="shelfTooltip">
        <h3 id="shelfTitle">Shelf A-12</h3>
        <div class="shelf-items" id="shelfItems">
            <!-- Items will be populated by JavaScript -->
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-dark py-5 mt-5">
        <div class="container">
            <div class="text-center">
                &copy; <?php echo date("Y"); ?> Xophia’s Inventory & Warehouse Management
            </div>
        </div>
    </footer>
    <!-- End Footer -->

    <!-- Bootstrap JS Bundle (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Animate on scroll for .main-container, .section-image-animate, .highlight, .layout-img, and .opt-list li
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
        animateOnScroll('.section-image-animate');
        animateOnScroll('.highlight');
        animateOnScroll('.layout-img');
        animateOnScroll('.opt-list li');
        
        // Warehouse Map Generation
        document.addEventListener('DOMContentLoaded', function() {
            const warehouseMap = document.getElementById('warehouseMap');
            const tooltip = document.getElementById('shelfTooltip');
            
            // Enhanced warehouse layout data with additional column and docks
            const layout = [
                // Row 0
                ['A1', 'A1', 'aisle', 'B1', 'B1', 'aisle', 'C1', 'C1', 'aisle', 'D1', 'D1', 'aisle', 'E1', 'E1'],
                // Row 1
                ['A2', 'A2', 'aisle', 'B2', 'B2', 'aisle', 'C2', 'C2', 'aisle', 'D2', 'D2', 'aisle', 'E2', 'E2'],
                // Row 2 (aisle)
                ['aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle'],
                // Row 3
                ['A3', 'A3', 'aisle', 'B3', 'B3', 'aisle', 'C3', 'C3', 'aisle', 'D3', 'D3', 'aisle', 'E3', 'E3'],
                // Row 4
                ['A4', 'A4', 'aisle', 'B4', 'B4', 'aisle', 'C4', 'C4', 'aisle', 'D4', 'D4', 'aisle', 'E4', 'E4'],
                // Row 5
                ['A5', 'A5', 'aisle', 'B5', 'B5', 'aisle', 'C5', 'C5', 'aisle', 'D5', 'D5', 'aisle', 'E5', 'E5'],
                // Row 6 (aisle)
                ['aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle'],
                // Row 7
                ['A6', 'A6', 'aisle', 'B6', 'B6', 'aisle', 'C6', 'C6', 'aisle', 'D6', 'D6', 'aisle', 'E6', 'E6'],
                // Row 8
                ['A7', 'A7', 'aisle', 'B7', 'B7', 'aisle', 'C7', 'C7', 'aisle', 'D7', 'D7', 'aisle', 'E7', 'E7'],
                // Row 9 (aisle)
                ['aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle', 'aisle'],
                // Row 10 (docks)
                ['unloading', 'unloading', 'aisle', 'aisle', 'loading', 'loading'],
                // Row 11 (docks)
                ['exit', 'exit']
            ];
            
            // Database items from PHP (converted to JSON)
            const dbItems = <?php echo json_encode($itemsByLocation); ?>;
            
            // Map database locations to our shelf identifiers
            const locationMap = {
                'RACK 1A': 'A1',
                'RACK 1-A': 'A1',
                'RACK 2A': 'A2',
                'RACK 3A': 'A3',
                'RACK 4A': 'A4',
                'RACK 5A': 'A5',
                'RACK 6A': 'A6',
                'RACK 7A': 'A7',
                'RACK 1B': 'B1',
                'RACK 2B': 'B2',
                'RACK 3B': 'B3',
                'RACK 4B': 'B4',
                'RACK 5B': 'B5',
                'RACK 6B': 'B6',
                'RACK 7B': 'B7',
                'RACK 1C': 'C1',
                'RACK 2C': 'C2',
                'RACK 3C': 'C3',
                'RACK 4C': 'C4',
                'RACK 5C': 'C5',
                'RACK 6C': 'C6',
                'RACK 7C': 'C7',
                'RACK 1D': 'D1',
                'RACK 2D': 'D2',
                'RACK 3D': 'D3',
                'RACK 4D': 'D4',
                'RACK 5D': 'D5',
                'RACK 6D': 'D6',
                'RACK 7D': 'D7',
                'RACK 1E': 'E1',
                'RACK 2E': 'E2',
                'RACK 3E': 'E3',
                'RACK 4E': 'E4',
                'RACK 5E': 'E5',
                'RACK 6E': 'E6',
                'RACK 7E': 'E7'
            };
            
            // Map categories to shelf types - 20 distinct categories
            const categoryShelfMap = {
                'ELECTRONICS': 'shelf-electronics',
                'TOOLS & HARDWARE': 'shelf-tools-hardware',
                'HOME APPLIANCES': 'shelf-home-appliances',
                'FURNITURE': 'shelf-furniture',
                'HEALTH & PERSONAL CARE': 'shelf-health-personal',
                'CLEANING & JANITORIAL SUPPLIES': 'shelf-cleaning-supplies',
                'PACKAGING & SHIPPING': 'shelf-packaging-shipping',
                'OFFICE & STATIONERY': 'shelf-office-stationery',
                'FOOD & BEVERAGES': 'shelf-food-beverages',
                'AUTOMOTIVE & SPARE PARTS': 'shelf-automotive-parts',
                'CLOTHING & APPAREL': 'shelf-clothing-apparel',
                'FOOTWEAR': 'shelf-footwear',
                'TOYS & GAMES': 'shelf-toys-games',
                'SPORTS & FITNESS EQUIPMENT': 'shelf-sports-fitness',
                'BOOKS & MEDIA': 'shelf-books-media',
                'INDUSTRIAL EQUIPMENT': 'shelf-industrial-equipment',
                'ELECTRICAL COMPONENTS': 'shelf-electrical-components',
                'PLUMBING SUPPLIES': 'shelf-plumbing-supplies',
                'GARDEN & OUTDOOR': 'shelf-garden-outdoor',
                'SAFETY & SECURITY EQUIPMENT': 'shelf-safety-security'
            };
            
            // Create shelf items object
            const shelfItems = {};
            
            // Map database items to our shelf identifiers
            for (const [location, items] of Object.entries(dbItems)) {
                const shelfCode = locationMap[location] || location;
                
                // Determine shelf type based on the first item's category
                const category = items[0]?.category?.toUpperCase() || 'OTHER';
                const shelfType = categoryShelfMap[category] || 'shelf-other';
                
                if (!shelfItems[shelfCode]) {
                    shelfItems[shelfCode] = {
                        items: [],
                        type: shelfType
                    };
                }
                
                items.forEach(item => {
                    shelfItems[shelfCode].items.push({
                        name: item.item,
                        quantity: item.stock,
                        category: item.category
                    });
                });
            }
            
            // Generate the warehouse map
            layout.forEach((row, rowIndex) => {
                row.forEach((cell, colIndex) => {
                    if (!cell) return; // Skip null cells
                    
                    const cellElement = document.createElement('div');
                    cellElement.className = 'map-cell';
                    
                    if (cell === 'aisle') {
                        cellElement.classList.add('aisle');
                        cellElement.textContent = '';
                    }
                    else if (cell === 'exit') {
                        cellElement.classList.add('exit');
                        cellElement.textContent = 'EXIT';
                    } 
                    else if (cell === 'unloading') {
                        cellElement.classList.add('unloading-dock');
                        cellElement.textContent = 'UNLOADING DOCK';
                    } 
                    else if (cell === 'loading') {
                        cellElement.classList.add('loading-dock');
                        cellElement.textContent = 'LOADING DOCK';
                    } 
                    else {
                        // Determine shelf type
                        const shelfType = shelfItems[cell]?.type || 'shelf-other';
                        cellElement.classList.add('shelf', shelfType);
                        cellElement.textContent = cell;
                        cellElement.dataset.shelf = cell;
                        
                        // Add hover event for shelf
                        cellElement.addEventListener('mouseenter', function(e) {
                            const shelf = this.dataset.shelf;
                            const items = shelfItems[shelf]?.items || [];
                            
                            // Position tooltip near the shelf
                            const rect = this.getBoundingClientRect();
                            tooltip.style.top = `${rect.bottom + window.scrollY}px`;
                            tooltip.style.left = `${rect.left + window.scrollX}px`;
                            
                            // Update tooltip content
                            document.getElementById('shelfTitle').textContent = `Shelf ${shelf}`;
                            
                            const itemsContainer = document.getElementById('shelfItems');
                            itemsContainer.innerHTML = '';
                            
                            if (items.length > 0) {
                                items.forEach(item => {
                                    const itemElement = document.createElement('div');
                                    itemElement.className = 'item';
                                    itemElement.innerHTML = `
                                        <span class="item-name">${item.name}</span>
                                        <span class="item-category">${item.category}</span>
                                        <span class="item-qty">${item.quantity} units</span>
                                    `;
                                    itemsContainer.appendChild(itemElement);
                                });
                            } else {
                                itemsContainer.innerHTML = '<div class="text-center py-2">No items in this shelf</div>';
                            }
                            
                            // Show tooltip
                            tooltip.style.display = 'block';
                        });
                        
                        cellElement.addEventListener('mouseleave', function() {
                            tooltip.style.display = 'none';
                        });
                    }
                    
                    warehouseMap.appendChild(cellElement);
                });
            });
            
            // Close tooltip when mouse leaves
            tooltip.addEventListener('mouseleave', function() {
                this.style.display = 'none';
            });
        });
    </script>
</body>
</html>