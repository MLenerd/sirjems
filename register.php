<?php
session_start();
include "config/config.php";

$error = "";
$success = "";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // --- PASSWORD STRENGTH VALIDATION ---
    // Regex breakdown:
    // (?=.*[a-z]) -> at least one lowercase
    // (?=.*[A-Z]) -> at least one uppercase
    // (?=.*\d)    -> at least one number
    // (?=.*[\W_]) -> at least one special character (symbol)
    // .{8,}       -> at least 8 characters long
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $special   = preg_match('@[^\w]@', $password);

    if(!$uppercase || !$lowercase || !$number || !$special || strlen($password) < 8) {
        $error = "Password must be at least 8 characters and include: 
                  <ul>
                    <li>One Uppercase Letter (A-Z)</li>
                    <li>One Lowercase Letter (a-z)</li>
                    <li>One Number (0-9)</li>
                    <li>One Special Character (!@#$%)</li>
                  </ul>";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $checkQuery = "SELECT * FROM users WHERE email = '$email'";
        $checkResult = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            $error = "This email is already registered.";
        } else {
            // Hash Password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert User (Status = pending, Role = staff)
            $insertQuery = "INSERT INTO users (first_name, last_name, email, password, status, role) 
                            VALUES ('$first_name', '$last_name', '$email', '$hashed_password', 'pending', 'staff')";
            
            if (mysqli_query($conn, $insertQuery)) {
                $success = "Account created! <strong>Please wait for Admin approval</strong> before logging in.";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Inventory System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f4f4; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .register-container { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        .register-header { text-align: center; margin-bottom: 30px; }
        .register-header h2 { font-weight: 700; color: #333; }
        .btn-black { background: #000; color: #fff; font-weight: 500; }
        .btn-black:hover { background: #333; color: #fff; }
        .password-hint { font-size: 0.75rem; color: #6c757d; margin-top: 5px; }
        /* Style for the error list */
        .alert ul { margin: 0; padding-left: 20px; text-align: left; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <i class="fa-solid fa-user-shield fa-3x mb-3"></i>
            <h2>Request Access</h2>
            <p class="text-muted">Create an account for admin approval</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger text-center py-2" role="alert">
                <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-info text-center py-2" role="alert">
                <i class="fa-solid fa-clock me-1"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">First Name</label>
                    <input type="text" class="form-control" name="first_name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Last Name</label>
                    <input type="text" class="form-control" name="last_name" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Email Address</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" class="form-control" name="password" required>
                    <div class="password-hint">Min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 symbol.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Confirm</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-black w-100 py-2 mb-3">Submit for Approval</button>
            
            <div class="text-center">
                <small class="text-muted">Already approved? <a href="login.php" class="text-decoration-none fw-bold text-dark">Login here</a></small>
            </div>
        </form>
    </div>
</body>
</html>