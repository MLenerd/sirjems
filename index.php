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
    <title>Inventory & Warehouse Management</title>
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
        .page-title {
            font-weight: 700;
            font-size: 64px;
            line-height: 77px;
            letter-spacing: -0.02em;
            color: #000;
        }
        .subtitle {
            font-size: 24px;
            color: rgba(0,0,0,0.75);
        }
        .section-title {
            font-size: 48px;
            font-weight: 600;
            letter-spacing: -0.02em;
        }
        .feature-title {
            font-size: 24px;
            font-weight: 500;
        }
        .feature-desc {
            font-size: 24px;
            color: #000;
            opacity: 0.75;
        }
        .card-feature {
            border: 1px solid #E6E6E6;
            border-radius: 12px;
            padding: 32px 20px;
            background: #fff;
            height: 100%;
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
        .testimonial-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            background: #D9D9D9;
        }
        .testimonial-name {
            font-size: 16px;
            font-weight: 500;
        }
        .testimonial-role {
            font-size: 16px;
            color: #828282;
        }
        .testimonial-quote {
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 1rem;
        }
        .testimonial-animate {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.7s, transform 0.7s;
        }
        .testimonial-animate.in-view {
            opacity: 1;
            transform: none;
        }
        .social-icons a {
            background: #fff;
            color: #828282;
            border-radius: 4px;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            font-size: 1.2rem;
            border: 1px solid #e6e6e6;
            transition: background 0.2s, color 0.2s;
        }
        .social-icons a:hover {
            background: #000;
            color: #fff;
        }
        .footer-link {
            color: #454545 !important;
            font-size: 16px;
            font-weight: 500;
            display: block;
            margin-bottom: 6px;
        }
        .footer-link.topic {
            color: #000 !important;
        }
        .main-container {
            margin: 2em auto;
            max-width: 1200px;
            background: #fff;
            padding: 2em;
            border-radius: 8px;
        }
        .divider {
            border-top: 1px solid #E6E6E6;
            margin: 2rem 0;
        }
        .navbar .nav-link .fa-bell {
            font-size: 1.2rem;
        }
        .navbar .dropdown-menu {
            font-size: 0.95rem;
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
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" onclick="markNotif()">
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
                                function markNotif() {
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
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fa-solid fa-right-from-bracket me-1"></i>Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- End Navigation -->

    <div class="container" style="margin-top: 120px;">
        <!-- Page Header Section -->
        <div class="row mb-5">
            <div class="col-lg-8 animate__animated animate__fadeInLeft">
                <div class="page-title mb-4">Inventory & Warehouse Management</div>   
                <div class="subtitle mb-4">
                    Warehouse inventory management is a process that involves receiving, storing, and tracking inventory in a warehouse; managing warehouse staff; and optimizing storage space and costs; all of which directly impacts fulfillment, shipping, and the customer experience.
                </div>
                <div class="social-icons mb-3">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>

        <hr class="divider">

        <div class="row mb-4 mb-5">
            <div class="col-12 text-center">
                <img 
                    src="img\index\image1.png"
                    alt="Warehouse Overview"
                    class="img-fluid rounded shadow section-image-animate"
                    style="max-height:700px;object-fit:cover;">
            </div>
        </div>

        <!-- Why warehouse inventory management is important -->
        <div class="row mb-5">
            <div class="col-lg-10 animate__animated animate__fadeInRight">
                <div class="section-title mb-4">Why is warehouse inventory management important to your business?</div>
                <div class="mb-4">
                    <div class="mb-3">
                        <span class="feature-title">Warehouse layout optimization</span>
                        <div class="feature-desc">Optimizing your warehouse space is the first thing to consider when managing a warehouse.</div>
                    </div>
                    <div class="mb-3">
                        <span class="feature-title">Warehouse inventory control tracking and recording</span>
                        <div class="feature-desc">Managing inventory is one of the most important parts of running an e-commerce business.</div>
                    </div>
                    <div class="mb-3">
                        <span class="feature-title">Picking and packing</span>
                        <div class="feature-desc">The picking and packing process should be optimized as much as possible to help support your picking and packing team and reduce human error.</div>
                    </div>
                    <div>
                        <span class="feature-title">Reporting and optimizing</span>
                        <div class="feature-desc">Investing in a warehouse management (WMS) can help you determine operational performance and offer inventory reports across the warehouse.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Processes Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="section-title text-center mb-5">4 warehouse inventory management processes</div>
            </div>
            <div class="d-flex flex-wrap justify-content-center gap-4">
                <!-- Stock Tracking Card -->
                <div class="card-feature" style="width:300px">
                    <img src="img\index\image2.png" class="img-fluid rounded mb-3" alt="Stock Tracking">
                    <div class="feature-title mb-2">Stock Tracking</div>
                    <div class="feature-desc mb-2">The process of monitoring inventory levels and movement within a warehouse.</div>
                </div>
                <!-- Inventory Valuation Card -->
                <div class="card-feature" style="width:300px">
                    <img src="img\index\image3.png" class="img-fluid rounded mb-3" alt="Inventory Valuation">
                    <div class="feature-title mb-2">Inventory Valuation</div>
                    <div class="feature-desc mb-2">Provides accurate financial insights for pricing, reporting, and tax compliance.</div>
                </div>
                <!-- Warehouse Layout Card -->
                <div class="card-feature" style="width:300px">
                    <img src="img\index\image4.png" class="img-fluid rounded mb-3" alt="Warehouse Layout">
                    <div class="feature-title mb-2">Warehouse Layout & Optimization</div>
                    <div class="feature-desc mb-2">Maximizes space and speeds up order fulfillment through smarter organization.</div>
                </div>
                <!-- Order Picking Card -->
                <div class="card-feature" style="width:300px">
                    <img src="img\index\image5.png" class="img-fluid rounded mb-3" alt="Order Picking">
                    <div class="feature-title mb-2">Order Picking, Packing & Shipping</div>
                    <div class="feature-desc mb-2">Ensures fast, accurate, and cost-efficient delivery to boost customer satisfaction.</div>
                </div>
            </div>
        </div>

        <!-- Testimonials -->
        <div class="row my-5">
            <div class="section-title mb-4">What our users say</div>
            <div class="d-flex flex-wrap gap-4">
                <div class="card-feature testimonial-animate" style="width:287px;">
                    <div class="testimonial-quote mb-4">“Janjan dingba”</div>
                    <div class="d-flex align-items-center gap-3">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" class="testimonial-avatar" alt="Jommel Sean B. Quilon">
                        <div>
                            <div class="testimonial-name">Jommel Sean B. Quilon</div>
                            <div class="testimonial-role">Warehouse Layout & Optimization Lead</div>
                        </div>
                    </div>
                </div>
                <div class="card-feature testimonial-animate" style="width:287px;">
                    <div class="testimonial-quote mb-4">“A fantastic bit of feedback”</div>
                    <div class="d-flex align-items-center gap-3">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" class="testimonial-avatar" alt="Jane Doe">
                        <div>
                            <div class="testimonial-name">Jane Doe</div>
                            <div class="testimonial-role">Logistics Supervisor</div>
                        </div>
                    </div>
                </div>
                <div class="card-feature testimonial-animate" style="width:287px;">
                    <div class="testimonial-quote mb-4">“A genuinely glowing review”</div>
                    <div class="d-flex align-items-center gap-3">
                        <img src="https://randomuser.me/api/portraits/men/85.jpg" class="testimonial-avatar" alt="Frank Ching">
                        <div>
                            <div class="testimonial-name">Frank Ching</div>
                            <div class="testimonial-role">Inventory Analyst</div>
                        </div>
                    </div>
                </div>
                <div class="card-feature testimonial-animate" style="width:299px;">
                    <div class="testimonial-quote mb-4">“AMAZING”</div>
                    <div class="d-flex align-items-center gap-3">
                        <img src="https://randomuser.me/api/portraits/women/65.jpg" class="testimonial-avatar" alt="Xophia Anne Z. Herrera">
                        <div>
                            <div class="testimonial-name">Xophia Anne Z. Herrera</div>
                            <div class="testimonial-role">Daga</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Creator comments -->
        <div class="row my-5">
            <div class="col-12 text-center">
                <span class="section-title">Creator Comments</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-dark py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="footer-link topic mb-1">Company</div>
                    <a href="#" class="footer-link">About Us</a>
                    <a href="#" class="footer-link">Careers</a>
                    <a href="#" class="footer-link">Contact</a>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="footer-link topic mb-1">Product</div>
                    <a href="#" class="footer-link">Features</a>
                    <a href="#" class="footer-link">Pricing</a>
                    <a href="#" class="footer-link">Demo</a>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="footer-link topic mb-1">Support</div>
                    <a href="#" class="footer-link">Help Center</a>
                    <a href="#" class="footer-link">Documentation</a>
                    <a href="#" class="footer-link">Forums</a>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="footer-link topic mb-1">Legal</div>
                    <a href="#" class="footer-link">Privacy Policy</a>
                    <a href="#" class="footer-link">Terms of Service</a>
                    <a href="#" class="footer-link">Cookie Policy</a>
                </div>
            </div>
            <div class="text-center mt-4">
                &copy; <?php echo date("Y"); ?> Inventory & Warehouse Management
            </div>
        </div>
    </footer>
    <!-- End Footer -->

    <!-- Bootstrap JS Bundle (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animate on scroll for .card-feature, .testimonial-animate, and .section-image-animate
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
        animateOnScroll('.testimonial-animate');
        animateOnScroll('.section-image-animate');
    </script>
</body>
</html>
