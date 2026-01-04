<?php
session_start();
include "config/config.php";

$error = "";
$lockout_msg = "";
$remaining_seconds = 0;

// 1. GET USER IP ADDRESS
$ip_address = $_SERVER['REMOTE_ADDR'];

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// --- CHECK IP LOCKOUT STATUS BEFORE FORM SUBMISSION ---
$checkIpQuery = "SELECT * FROM login_attempts WHERE ip_address = '$ip_address'";
$ipResult = mysqli_query($conn, $checkIpQuery);
$ipData = mysqli_fetch_assoc($ipResult);

if ($ipData) {
    $failed_attempts = $ipData['attempts'];
    $last_failed_time = $ipData['last_attempt_time'];

    // Lockout Logic
    $lockout_duration = 0;
    if ($failed_attempts >= 3) {
        $exponent = $failed_attempts - 3;
        $lockout_duration = 30 * pow(2, $exponent);
    }

    if ($failed_attempts >= 3 && $last_failed_time) {
        $last_time_ts = strtotime($last_failed_time);
        $current_time = time();
        $time_passed = $current_time - $last_time_ts;

        if ($time_passed < $lockout_duration) {
            // CALCULATE REMAINING SECONDS
            $remaining_seconds = $lockout_duration - $time_passed;
            $lockout_msg = "Too many failed attempts.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($remaining_seconds > 0) {
        $error = "You device is temporarily locked. Please wait for the timer to finish.";
    } else {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
             $error = "Please enter both email and password.";
        } else {
            $query = "SELECT * FROM users WHERE email = '$email'";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);

                if (password_verify($password, $user['password'])) {
                    
                    if ($user['status'] === 'pending') {
                        $error = "Your account is currently pending administrator approval. You will be notified once approved.";
                    } elseif ($user['status'] === 'rejected') {
                        $error = "Your account application has been rejected. Please contact support for more information.";
                    } else {
                        $resetIpQuery = "DELETE FROM login_attempts WHERE ip_address = '$ip_address'";
                        mysqli_query($conn, $resetIpQuery);

                        session_regenerate_id(true);

                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['fullname'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['role'] = $user['role'];

                        if ($user['role'] === 'admin') {
                            header("Location: admin_users.php");
                        } else {
                            header("Location: index.php");
                        }
                        exit;
                    }

                } else {
                    $error = "The password you entered is incorrect. Please try again."; 
                    handleFailedAttempt($conn, $ip_address);             
                }
            } else {
                $error = "We couldn't find an account associated with that email address.";
                handleFailedAttempt($conn, $ip_address);
            }
        }
    }
}

function handleFailedAttempt($conn, $ip) {
    $now = date('Y-m-d H:i:s');
    $check = mysqli_query($conn, "SELECT * FROM login_attempts WHERE ip_address = '$ip'");
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        $new_attempts = $row['attempts'] + 1;
        mysqli_query($conn, "UPDATE login_attempts SET attempts = $new_attempts, last_attempt_time = '$now' WHERE ip_address = '$ip'");
    } else {
        mysqli_query($conn, "INSERT INTO login_attempts (ip_address, attempts, last_attempt_time) VALUES ('$ip', 1, '$now')");
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
            background: #ffffff; /* White background */
            color: #02381e; /* Dark green text */
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .login-container { 
            background: #ffffff; /* White background */
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(2, 56, 30, 0.15); /* Dark green shadow */
            border: 2px solid #02381e; /* Dark green border */
            width: 100%; 
            max-width: 400px; 
        }
        .login-header { 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .login-header h2 { 
            font-weight: 700; 
            color: #02381e; /* Dark green */
        }
        .login-header p {
            color: rgba(2, 56, 30, 0.7); /* Dark green with transparency */
        }
        
        /* Button Styling */
        .btn-black { 
            background: #02381e !important; /* Dark green */
            color: #ffffff !important; /* White text */
            font-weight: 500; 
            border: 2px solid #02381e;
        }
        .btn-black:hover { 
            background: #012916 !important; /* Darker green */
            border-color: #012916 !important;
            color: #ffffff !important;
        }
        
        /* Form Input Styling */
        .form-control {
            border: 2px solid #02381e; /* Dark green border */
            color: #02381e; /* Dark green text */
        }
        .form-control:focus {
            border-color: #c19802; /* Gold focus */
            box-shadow: 0 0 0 0.25rem rgba(193, 152, 2, 0.25); /* Gold shadow */
        }
        
        .input-group-text {
            background-color: rgba(2, 56, 30, 0.1) !important; /* Light green */
            border: 2px solid #02381e !important; /* Dark green border */
            color: #02381e !important; /* Dark green text */
        }
        
        /* Countdown styling */
        #countdown-box { 
            display: none; 
            font-weight: bold; 
            color: #dc3545; 
            margin-bottom: 15px; 
            text-align: center; 
            border: 1px solid #f5c6cb; 
            background: #f8d7da; 
            padding: 10px; 
            border-radius: 6px; 
        }
        
        /* Alert Styling */
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid #dc3545;
            color: #dc3545;
        }
        
        /* Icon Color */
        .fa-warehouse {
            color: #02381e; /* Dark green icon */
        }
        
        /* Register link */
        .text-dark {
            color: #c19802 !important; /* Gold for register link */
        }
        .text-dark:hover {
            color: #02381e !important; /* Dark green on hover */
        }
        
        /* Smooth error animation */
        .alert {
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Labels */
        .form-label {
            color: #02381e; /* Dark green */
        }
        
        /* Text muted */
        .text-muted {
            color: rgba(2, 56, 30, 0.7) !important;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fa-solid fa-warehouse fa-3x mb-3"></i>
            <h2>Welcome Back</h2>
            <p>Login to manage your inventory</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger text-center py-2" role="alert">
                <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div id="countdown-box">
            Account Locked. Try again in <span id="timer">0</span>s
        </div>

        <fieldset id="loginFieldset">
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-bold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" class="form-control" name="email" placeholder="name@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-black w-100 py-2 mb-3">Sign In</button>
                
                <div class="text-center">
                    <small class="text-muted">Don't have an account? <a href="register.php" class="text-decoration-none fw-bold text-dark">Register here</a></small>
                </div>
            </form>
        </fieldset>
    </div>

    <script>
        let timeLeft = <?php echo $remaining_seconds; ?>;
        
        const countdownBox = document.getElementById('countdown-box');
        const timerSpan = document.getElementById('timer');
        const fieldset = document.getElementById('loginFieldset');

        if (timeLeft > 0) {
            countdownBox.style.display = 'block';
            fieldset.disabled = true;
            timerSpan.innerText = timeLeft;

            const interval = setInterval(() => {
                timeLeft--;
                timerSpan.innerText = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(interval);
                    countdownBox.style.display = 'none';
                    fieldset.disabled = false;
                }
            }, 1000);
        }
    </script>
</body>
</html>