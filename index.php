<?php
session_start();

// --- SECURITY BLOCK ---
// If user is not logged in, kick them to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Session Timeout (15 minutes)
$timeout_duration = 900; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?error=timeout");
    exit;
}
$_SESSION['last_activity'] = time(); // Update activity time

// Anti-Session Hijacking (Regenerate ID every 5 mins)
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 300) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
// -----------------------

include "config/config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Puregold Inventory System</title>
    <link rel="icon" href="img/ico/logo.ico"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Base Styles */
        html { scroll-behavior: smooth; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #ffffff; /* White background */
            color: #02381e; /* Dark green for text */
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
            color: #c19802 !important; /* New gold for active/hover */
        }
        .navbar-brand { 
            font-size: 20px; 
            font-weight: 700; 
        }
        
        /* Typography */
        .page-title { 
            font-weight: 700; 
            font-size: 64px; 
            line-height: 1.2; 
            letter-spacing: -0.02em; 
            color: #02381e; /* Dark green */
        }
        .subtitle { 
            font-size: 24px; 
            color: rgba(2, 56, 30, 0.85); /* Dark green with transparency */
        }
        .section-title { 
            font-size: 48px; 
            font-weight: 600; 
            letter-spacing: -0.02em; 
            color: #02381e; /* Dark green */
        }
        
        /* Cards */
        .card-feature {
            border: 2px solid #02381e; /* Dark green border */
            border-radius: 12px;
            padding: 24px;
            background: #ffffff; /* White background */
            box-shadow: 0 4px 12px 0 rgba(2, 56, 30, 0.15); /* Dark green shadow */
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            height: 100%;
            text-decoration: none;
            color: #02381e; /* Dark green text */
            display: block;
        }
        .card-feature:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(2, 56, 30, 0.25);
            border-color: #c19802; /* New gold border on hover */
        }
        .feature-icon { 
            font-size: 2rem; 
            margin-bottom: 1rem; 
            color: #c19802; /* New gold icons */
        }
        .feature-title { 
            font-size: 20px; 
            font-weight: 600; 
            margin-bottom: 8px; 
            color: #02381e; /* Dark green */
        }
        .feature-desc { 
            font-size: 16px; 
            color: rgba(2, 56, 30, 0.75); /* Dark green with transparency */
            margin-bottom: 1rem; 
        }
        
        /* Buttons */
        .btn-dark {
            background-color: #02381e !important; /* Dark green */
            border-color: #02381e !important;
            color: #ffffff !important; /* White text */
        }
        .btn-dark:hover {
            background-color: #012916 !important; /* Darker green */
            border-color: #012916 !important;
        }
        
        .btn-primary, .text-primary {
            color: #c19802 !important; /* New gold for primary actions */
        }
        
        /* Search Card */
        .card {
            border: 2px solid #02381e; /* Dark green border */
            background: #ffffff; /* White background */
            box-shadow: 0 4px 12px 0 rgba(2, 56, 30, 0.15);
        }
        
        /* Form Inputs */
        .form-control {
            border: 2px solid #02381e; /* Dark green border */
            color: #02381e; /* Dark green text */
        }
        .form-control:focus {
            border-color: #c19802; /* New gold focus */
            box-shadow: 0 0 0 0.25rem rgba(193, 152, 2, 0.25); /* New gold shadow */
        }
        
        /* Logout link */
        .nav-link.text-danger {
            color: #c19802 !important; /* New gold for logout */
        }
        .nav-link.text-danger:hover {
            color: #02381e !important; /* Dark green on hover */
        }
        
        /* Animation Classes */
        .fade-in-up {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        .fade-in-up.visible { opacity: 1; transform: translateY(0); }
        
        /* Additional color utilities */
        .bg-primary { background-color: #02381e !important; } /* Dark green */
        .bg-secondary { background-color: #c19802 !important; } /* New gold */
        .bg-light { background-color: #ffffff !important; } /* White */
        
        .text-primary { color: #c19802 !important; } /* New gold */
        .text-secondary { color: #02381e !important; } /* Dark green */
        .text-light { color: #ffffff !important; } /* White */
        
        /* Card title in search box */
        .card h5 {
            color: #02381e !important;
        }
        
        /* Arrow icons in feature cards */
        .fa-arrow-right {
            color: #c19802 !important;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top py-3">
        <div class="container-fluid">
            <a class="navbar-brand ps-4" href="index.php" style="color: #c19802 !important;">
                <img src="img\logo.jpg" alt="Logo" height="30" class="me-2">
                Puregold Inventory System
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
                        <a class="nav-link text-danger text-decoration-underline" href="logout.php"> <i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
                    </li>
                    
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 120px;">
        
        <div class="row mb-5 fade-in-up">
            <div class="col-lg-8">
                <div class="page-title mb-2">Dashboard</div>   
                <div class="subtitle">
                    Welcome back, <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong>. 
                    <br>Select a module below to begin.
                </div>
            </div>
        </div>

        <div class="row mb-5 justify-content-center fade-in-up">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold mb-3" style="color: #02381e;"><i class="fa-solid fa-magnifying-glass me-2" style="color: #c19802;"></i>Find Item by Barcode</h5>
                    <form action="view.php" method="GET" class="d-flex gap-2">
                        <input type="text" name="barcode" class="form-control form-control-lg" placeholder="Scan or type barcode (e.g. PG-001)..." required autofocus>
                        <button type="submit" class="btn btn-dark btn-lg px-4">Search</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            
            <div class="col-md-6 col-lg-4 fade-in-up">
                <a href="stock-tracking.php" class="card-feature">
                    <div class="feature-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <div class="feature-title">Stock Tracking</div>
                    <div class="feature-desc">Monitor real-time inventory levels, locations, and status across your warehouse.</div>
                    <div class="text-primary fw-bold">Manage Stock <i class="fa-solid fa-arrow-right ms-1"></i></div>
                </a>
            </div>

            <div class="col-md-6 col-lg-4 fade-in-up">
                <a href="warehouse-layout-optimization.php" class="card-feature">
                    <div class="feature-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                    <div class="feature-title">Warehouse Layout</div>
                    <div class="feature-desc">Visualize and optimize storage locations to improve retrieval efficiency.</div>
                    <div class="text-primary fw-bold">View Layout <i class="fa-solid fa-arrow-right ms-1"></i></div>
                </a>
            </div>

            <div class="col-md-6 col-lg-4 fade-in-up">
                <a href="inventory-valuation.php" class="card-feature">
                    <div class="feature-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <div class="feature-title">Inventory Valuation</div>
                    <div class="feature-desc">Calculate total asset value and generate financial reports.</div>
                    <div class="text-primary fw-bold">View Reports <i class="fa-solid fa-arrow-right ms-1"></i></div>
                </a>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
        // 1. Fade-in animation (unchanged)
        document.addEventListener("DOMContentLoaded", function() {
            const elements = document.querySelectorAll('.fade-in-up');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.classList.add('visible');
                }, index * 100); 
            });
        });

        // 2. Dynamic Idle Timer
        const timeoutDuration = <?php echo $timeout_duration; ?>; // from PHP (in seconds)
        const timeoutLimit = timeoutDuration * 1000; // convert to milliseconds
        let idleTimer;

        function resetIdleTimer() {
            // Stop the previous countdown
            clearTimeout(idleTimer);

            // Start a new countdown
            idleTimer = setTimeout(function() {
                alert("Idled for too long");
                window.location.href = 'logout.php?reason=timeout'; 
            }, timeoutLimit);
        }

        // Listen for user activity to reset the timer
        window.addEventListener('mousemove', resetIdleTimer);
        window.addEventListener('keypress', resetIdleTimer);
        window.addEventListener('click', resetIdleTimer);
        window.addEventListener('scroll', resetIdleTimer);

        // Start the timer when the page loads
        resetIdleTimer();
    </script>
</html>