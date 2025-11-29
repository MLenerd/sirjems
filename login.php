<?php
session_start();
include "config/config.php";

$error = "";
$lockout_msg = "";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // 1. Check if user exists
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $failed_attempts = $user['failed_attempts'];
        $last_failed_time = $user['last_failed_time'];
        
        // --- LOCKOUT LOGIC START ---
        $lockout_duration = 0;
        if ($failed_attempts >= 3) {
            // Formula: 30s * 2^(attempts - 3)
            // Attempt 3 = 30s, Attempt 4 = 60s, Attempt 5 = 120s...
            $exponent = $failed_attempts - 3;
            $lockout_duration = 30 * pow(2, $exponent);
        }

        $is_locked = false;
        if ($failed_attempts >= 3 && $last_failed_time) {
            $last_time_ts = strtotime($last_failed_time);
            $current_time = time();
            $time_passed = $current_time - $last_time_ts;

            if ($time_passed < $lockout_duration) {
                $is_locked = true;
                $wait_time = $lockout_duration - $time_passed;
                
                // Format wait time nicely
                if($wait_time > 60) {
                    $mins = floor($wait_time / 60);
                    $secs = $wait_time % 60;
                    $time_str = "$mins min $secs sec";
                } else {
                    $time_str = "$wait_time sec";
                }
                
                $lockout_msg = "Too many failed attempts. Please wait $time_str before trying again.";
            }
        }
        // --- LOCKOUT LOGIC END ---

        if (!$is_locked) {
            // Verify Password
            if (password_verify($password, $user['password'])) {
                // Success: Reset attempts and log in
                $resetQuery = "UPDATE users SET failed_attempts = 0, last_failed_time = NULL WHERE id = " . $user['id'];
                mysqli_query($conn, $resetQuery);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
                exit;
            } else {
                // Failure: Increment attempts
                $new_attempts = $failed_attempts + 1;
                $now = date('Y-m-d H:i:s');
                $updateQuery = "UPDATE users SET failed_attempts = $new_attempts, last_failed_time = '$now' WHERE id = " . $user['id'];
                mysqli_query($conn, $updateQuery);

                // Check if this specific failure triggered a lockout to warn the user immediately
                if ($new_attempts >= 3) {
                    $next_wait = 30 * pow(2, $new_attempts - 3);
                     if($next_wait > 60) {
                        $mins = floor($next_wait / 60);
                        $secs = $next_wait % 60;
                        $time_str = "$mins min $secs sec";
                    } else {
                        $time_str = "$next_wait sec";
                    }
                    $error = "Incorrect password. You are now locked out for $time_str.";
                } else {
                    $attempts_left = 3 - $new_attempts;
                    $error = "Incorrect password. You have $attempts_left attempt(s) left before lockout.";
                }
            }
        } else {
            // User tried to login while locked out
            // Optional: You could extend the timer here, but usually we just show the message
            $error = $lockout_msg;
        }

    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Inventory System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f4f4f4;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            font-weight: 700;
            color: #333;
        }
        .btn-black {
            background: #000;
            color: #fff;
            font-weight: 500;
        }
        .btn-black:hover {
            background: #333;
            color: #fff;
        }
        .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 0 0.25rem rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="login-container animate__animated animate__fadeIn">
        <div class="login-header">
            <i class="fa-solid fa-warehouse fa-3x mb-3"></i>
            <h2>Welcome Back</h2>
            <p class="text-muted">Login to manage your inventory</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger text-center py-2" role="alert">
                <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label fw-bold">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-user"></i></span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label fw-bold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-black w-100 py-2 mb-3">Sign In</button>
            
            <div class="text-center">
                <small class="text-muted">Don't have an account? <a href="register.php" class="text-decoration-none fw-bold text-dark">Register here</a></small>
            </div>
        </form>
    </div>
</body>
</html>