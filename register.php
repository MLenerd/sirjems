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

    // --- NAME VALIDATION (Letters only) ---
    if (!preg_match('/^[A-Za-z\s]+$/', $first_name)) {
        $error = "First name must contain letters only, no numbers or symbols.";
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $last_name)) {
        $error = "Last name must contain letters only, no numbers or symbols.";
    }
    // --- PASSWORD STRENGTH VALIDATION ---
    elseif(!preg_match('@[A-Z]@', $password) || !preg_match('@[a-z]@', $password) || 
           !preg_match('@[0-9]@', $password) || !preg_match('@[^\w]@', $password) || 
           strlen($password) < 8) {
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
        
        /* Real-time validation styles */
        .validation-item { 
            font-size: 0.875rem; 
            margin: 5px 0; 
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-5px);
        }
        .validation-item.show {
            opacity: 1;
            transform: translateY(0);
        }
        .validation-item i { margin-right: 8px; }
        .validation-item.invalid { color: #dc3545; }
        .validation-item.valid { color: #28a745; font-weight: 500; }
        
        .validation-box { 
            background: #f8f9fa; 
            border-radius: 8px; 
            padding: 15px; 
            margin-top: 10px;
            border: 1px solid #dee2e6;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.4s ease, opacity 0.3s ease, margin-top 0.3s ease, padding 0.3s ease;
        }
        
        .validation-box.show {
            max-height: 300px;
            opacity: 1;
            margin-top: 10px;
            padding: 15px;
        }
        
        .match-feedback { 
            font-size: 0.875rem; 
            margin-top: 5px; 
            font-weight: 500;
            opacity: 0;
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        .match-feedback.show {
            opacity: 1;
            transform: translateY(0);
        }
        .match-feedback.valid { color: #28a745; }
        .match-feedback.invalid { color: #dc3545; }
        
        .name-feedback {
            font-size: 0.875rem;
            margin-top: 5px;
            font-weight: 500;
            opacity: 0;
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        .name-feedback.show {
            opacity: 1;
            transform: translateY(0);
        }
        .name-feedback.valid { color: #28a745; }
        .name-feedback.invalid { color: #dc3545; }
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
                    <input type="text" class="form-control" name="first_name" id="first_name" required>
                    <div id="firstNameFeedback" class="name-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Last Name</label>
                    <input type="text" class="form-control" name="last_name" id="last_name" required>
                    <div id="lastNameFeedback" class="name-feedback"></div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Email Address</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                    <div class="password-hint">Min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 symbol.</div>
                    
                    <div class="validation-box" id="passwordValidation">
                        <div class="validation-item invalid" id="length-check">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <span>At least 8 characters</span>
                        </div>
                        <div class="validation-item invalid" id="uppercase-check">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <span>One uppercase letter (A-Z)</span>
                        </div>
                        <div class="validation-item invalid" id="lowercase-check">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <span>One lowercase letter (a-z)</span>
                        </div>
                        <div class="validation-item invalid" id="number-check">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <span>One number (0-9)</span>
                        </div>
                        <div class="validation-item invalid" id="special-check">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <span>One special character (!@#$%)</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Confirm</label>
                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                    <div id="matchFeedback" class="match-feedback"></div>
                </div>
            </div>

            <button type="submit" class="btn btn-black w-100 py-2 mb-3">Submit for Approval</button>
            
            <div class="text-center">
                <small class="text-muted">Already approved? <a href="login.php" class="text-decoration-none fw-bold text-dark">Login here</a></small>
            </div>
        </form>
    </div>

    <script>
        const firstName = document.getElementById('first_name');
        const lastName = document.getElementById('last_name');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordValidation = document.getElementById('passwordValidation');

        // Password validation checks
        let validations = {
            length: false,
            uppercase: false,
            lowercase: false,
            number: false,
            special: false
        };

        // Validate name fields (letters only)
        function validateName(input, feedbackId) {
            const value = input.value.trim();
            const feedback = document.getElementById(feedbackId);
            const lettersOnly = /^[A-Za-z\s]+$/;
            
            if (value.length === 0) {
                feedback.classList.remove('show', 'valid', 'invalid');
                return;
            }
            
            if (lettersOnly.test(value)) {
                feedback.textContent = '✓ Valid name';
                feedback.classList.remove('invalid');
                feedback.classList.add('valid', 'show');
            } else {
                feedback.textContent = '✗ Letters only, no numbers or symbols';
                feedback.classList.remove('valid');
                feedback.classList.add('invalid', 'show');
            }
        }

        // First name validation
        firstName.addEventListener('input', function() {
            validateName(this, 'firstNameFeedback');
        });

        // Last name validation
        lastName.addEventListener('input', function() {
            validateName(this, 'lastNameFeedback');
        });

        // Show validation box when user starts typing password
        password.addEventListener('input', function() {
            const value = this.value;
            
            // Show validation box with smooth animation
            if (value.length > 0) {
                passwordValidation.classList.add('show');
                // Add show class to each validation item with slight delay
                setTimeout(() => {
                    document.querySelectorAll('.validation-item').forEach((item, index) => {
                        setTimeout(() => {
                            item.classList.add('show');
                        }, index * 50);
                    });
                }, 100);
            } else {
                passwordValidation.classList.remove('show');
                document.querySelectorAll('.validation-item').forEach(item => {
                    item.classList.remove('show');
                });
            }
            
            // Check length
            validations.length = value.length >= 8;
            updateCheck('length-check', validations.length);
            
            // Check uppercase
            validations.uppercase = /[A-Z]/.test(value);
            updateCheck('uppercase-check', validations.uppercase);
            
            // Check lowercase
            validations.lowercase = /[a-z]/.test(value);
            updateCheck('lowercase-check', validations.lowercase);
            
            // Check number
            validations.number = /[0-9]/.test(value);
            updateCheck('number-check', validations.number);
            
            // Check special character
            validations.special = /[^A-Za-z0-9]/.test(value);
            updateCheck('special-check', validations.special);
            
            // Check password match if confirm password has value
            if (confirmPassword.value.length > 0) {
                checkPasswordMatch();
            }
        });

        // Real-time confirm password validation
        confirmPassword.addEventListener('input', checkPasswordMatch);

        function checkPasswordMatch() {
            const matchFeedback = document.getElementById('matchFeedback');
            
            if (confirmPassword.value.length === 0) {
                matchFeedback.classList.remove('show', 'valid', 'invalid');
            } else {
                const isMatch = password.value === confirmPassword.value;
                
                if (isMatch) {
                    matchFeedback.textContent = '✓ Passwords match';
                    matchFeedback.classList.remove('invalid');
                    matchFeedback.classList.add('valid', 'show');
                } else {
                    matchFeedback.textContent = '✗ Passwords do not match';
                    matchFeedback.classList.remove('valid');
                    matchFeedback.classList.add('invalid', 'show');
                }
            }
        }

        // Update individual validation check
        function updateCheck(elementId, isValid) {
            const element = document.getElementById(elementId);
            const icon = element.querySelector('i');
            
            if (isValid) {
                element.classList.remove('invalid');
                element.classList.add('valid');
                icon.className = 'fa-solid fa-circle-check';
            } else {
                element.classList.remove('valid');
                element.classList.add('invalid');
                icon.className = 'fa-solid fa-circle-xmark';
            }
        }
    </script>
</body>
</html>