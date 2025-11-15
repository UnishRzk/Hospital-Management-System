<?php

session_start();

// If user is already logged in, redirect them away from signup page
if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
    if ($_SESSION['role'] === 'patient') {
        header("Location: ../Patient/patient_dashboard.php");
        exit();
    }
    if ($_SESSION['role'] === 'doctor') {
        header("Location: ../Doctor/doctor_dashboard.php");
        exit();
    }
    if ($_SESSION['role'] === 'nurse') {
        header("Location: ../Nurse/nurse_dashboard.php");
        exit();
    }
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../Admin/admin_dashboard.php");
        exit();
    }
}

include("../config/db.php");

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'];
    
    // Set role to 'patient' automatically
    $role = 'patient';
    
    // Validate inputs
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($full_name) || empty($phone) || empty($gender)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username=?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error = "Username already exists";
        } else {
            // Start transaction for user and patient creation
            $conn->begin_transaction();
            
            try {
                // Hash password and insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_user_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $insert_user_stmt->bind_param("sss", $username, $hashed_password, $role);
                
                if ($insert_user_stmt->execute()) {
                    $user_id = $conn->insert_id;
                    
                    // Insert patient record
                    $insert_patient_stmt = $conn->prepare("INSERT INTO patients (user_id, name, email, gender, phone) VALUES (?, ?, ?, ?, ?)");
                    $insert_patient_stmt->bind_param("issss", $user_id, $full_name, $email, $gender, $phone);
                    
                    if ($insert_patient_stmt->execute()) {
                        $conn->commit();
                        $success = "Patient account created successfully! You can now login.";
                        // Clear form
                        $_POST = array();
                    } else {
                        throw new Exception("Error creating patient record");
                    }
                    $insert_patient_stmt->close();
                } else {
                    throw new Exception("Error creating user account");
                }
                $insert_user_stmt->close();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Error creating account. Please try again.";
            }
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration - SwasthyaTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script type="text/javascript">
    // Prevent page caching and back navigation
    window.history.forward();
    
    function preventBack() {
        window.history.forward();
    }
    
    setTimeout(preventBack, 0);
    window.onunload = function() { null };
    
    // Clear form data when page is loaded
    window.onload = function() {
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        
        // Add focus effects to form inputs
        const inputs = document.querySelectorAll('.form-input, .form-select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '' && !this.classList.contains('form-select')) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
        
        // Toggle password visibility
        const togglePassword = document.querySelectorAll('.toggle-password');
        
        togglePassword.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const target = this.getAttribute('data-target');
                const input = document.querySelector(`input[name="${target}"]`);
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
        
        // Password strength indicator
        const passwordInputElement = document.querySelector('input[name="password"]');
        const strengthIndicator = document.querySelector('.password-strength');
        
        if (passwordInputElement && strengthIndicator) {
            passwordInputElement.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password.length >= 6) strength++;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
                if (password.match(/\d/)) strength++;
                if (password.match(/[^a-zA-Z\d]/)) strength++;
                
                const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
                const strengthColors = ['#f31026', '#ff6b6b', '#ffa500', '#4ecdc4', '#2ecc71'];
                
                strengthIndicator.textContent = strengthText[strength];
                strengthIndicator.style.color = strengthColors[strength];
                strengthIndicator.className = 'password-strength';
                
                // Add visual bars
                const bars = document.querySelectorAll('.strength-bar');
                bars.forEach((bar, index) => {
                    bar.style.background = index < strength ? strengthColors[strength] : '#e0e0e0';
                });
            });
        }
        
        // Phone number formatting
        const phoneInput = document.querySelector('input[name="phone"]');
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                // Remove any non-digit characters
                this.value = this.value.replace(/\D/g, '');
                
                // Format as (XXX) XXX-XXXX if 10 digits
                if (this.value.length === 10) {
                    this.value = this.value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                }
            });
        }
    };
    
    function validateForm() {
        const password = document.querySelector('input[name="password"]').value;
        const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
        const phone = document.querySelector('input[name="phone"]').value;
        
        if (password !== confirmPassword) {
            alert("Passwords do not match!");
            return false;
        }
        
        if (password.length < 6) {
            alert("Password must be at least 6 characters long!");
            return false;
        }
        
        // Basic phone validation (at least 10 digits)
        const phoneDigits = phone.replace(/\D/g, '');
        if (phoneDigits.length < 10) {
            alert("Please enter a valid phone number!");
            return false;
        }
        
        return true;
    }
</script>
    <style>
        :root {
            --primary-blue: #015eac;
            --primary-red: #f31026;
            --text-dark: #000000;
            --text-light: #ffffff;
            --background-light: #f9f9fb;
            --background-white: #ffffff;
            --gradient-light: linear-gradient(120deg, #e8f0ff, #f9f9fb);
            --shadow-light: 0 2px 10px rgba(0,0,0,0.05);
            --shadow-medium: 0 6px 16px rgba(0,0,0,0.08);
            --shadow-heavy: 0 10px 24px rgba(0,0,0,0.12);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Roboto', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            line-height: 1.6;
            color: var(--text-dark);
            background: var(--background-light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Header */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-light);
            padding: 0.8rem 0;
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .logo-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-img {
            height: 30px;
            width: 40px;
            vertical-align: middle;
        }

        nav {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-dark);
            transition: var(--transition);
            position: relative;
            padding: 0.5rem 0;
        }

        .nav-link:hover {
            color: var(--primary-red);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-red);
            transition: var(--transition);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .btn-login {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border: 1px solid var(--primary-blue);
            border-radius: var(--border-radius);
            color: var(--primary-blue);
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-login:hover {
            background: var(--primary-blue);
            color: var(--text-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        /* Signup Section */
        .signup-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: var(--gradient-light);
            position: relative;
            overflow: hidden;
        }

        .signup-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('../images/login-bg-pattern.png') no-repeat center;
            background-size: cover;
            opacity: 0.03;
            z-index: 0;
        }

        .signup-container {
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 1000px;
            width: 100%;
            z-index: 1;
            position: relative;
        }

        .signup-card {
            background: var(--background-white);
            padding: 3rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-heavy);
            text-align: center;
            animation: fadeInUp 0.8s ease;
            position: relative;
            width: 100%;
            max-width: 500px;
        }

        .signup-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary-blue);
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .signup-header {
            margin-bottom: 2rem;
        }

        .signup-icon {
            width: 70px;
            height: 70px;
            background: rgba(1, 94, 172, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: var(--primary-blue);
            font-size: 1.8rem;
        }

        .signup-card h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            color: var(--primary-blue);
        }

        .signup-subtitle {
            color: #666;
            font-size: 0.95rem;
            max-width: 400px;
            margin: 0 auto;
        }

        .patient-badge {
            display: inline-block;
            background: rgba(1, 94, 172, 0.1);
            color: var(--primary-blue);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        .signup-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
        }

        .form-group {
            position: relative;
            text-align: left;
            flex: 1;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background: #f9f9f9;
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }

        .form-input:focus, .form-select:focus {
            border-color: var(--primary-blue);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(1, 94, 172, 0.1);
            outline: none;
        }

        .form-group.focused .form-input, .form-group.focused .form-select {
            border-color: var(--primary-blue);
            background: #fff;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
            transition: var(--transition);
        }

        .form-group.focused .input-icon {
            color: var(--primary-blue);
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
            cursor: pointer;
            transition: var(--transition);
        }

        .toggle-password:hover {
            color: var(--primary-blue);
        }

        .password-strength-container {
            margin-top: 0.5rem;
        }

        .strength-bars {
            display: flex;
            gap: 3px;
            margin-bottom: 0.3rem;
        }

        .strength-bar {
            height: 4px;
            flex: 1;
            background: #e0e0e0;
            border-radius: 2px;
            transition: var(--transition);
        }

        .password-strength {
            font-size: 0.8rem;
            text-align: left;
            font-weight: 500;
        }

        .signup-btn {
            background: var(--primary-blue);
            color: var(--text-light);
            border: none;
            padding: 1rem;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .signup-btn:hover {
            background: #1f4fbf;
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .signup-btn:active {
            transform: translateY(0);
        }

        .error-message {
            background: rgba(243, 16, 38, 0.1);
            color: var(--primary-red);
            padding: 0.8rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            font-size: 0.9rem;
            border-left: 4px solid var(--primary-red);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .success-message {
            background: rgba(46, 204, 113, 0.1);
            color: #27ae60;
            padding: 0.8rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            font-size: 0.9rem;
            border-left: 4px solid #27ae60;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .signup-footer {
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #666;
        }

        .signup-footer a {
            color: var(--primary-blue);
            font-weight: 500;
            transition: var(--transition);
        }

        .signup-footer a:hover {
            color: var(--primary-red);
        }

        /* Footer */
        footer {
            background: var(--primary-blue);
            color: var(--text-light);
            text-align: center;
            padding: 1.5rem;
        }

        /* Brand Colors */
        .swasthya-color {
            color: var(--primary-blue);
        }

        .track-color {
            color: var(--primary-red);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {opacity: 0; transform: translateY(30px);}
            to {opacity: 1; transform: translateY(0);}
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .signup-card {
                padding: 2rem 1.5rem;
            }
            
            .form-row {
                flex-direction: column;
                gap: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .signup-card {
                padding: 1.5rem;
            }
            
            .signup-card h2 {
                font-size: 1.5rem;
            }
            
            .form-input, .form-select {
                padding: 0.9rem 0.9rem 0.9rem 2.8rem;
            }
            
            .input-icon {
                left: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="../index.php" class="logo-link">
                    <img class="nav-img" src="../images/nav-logo.png" alt="SwasthyaTrack Logo">
                    <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
                </a>
            </div>
            <nav>
                <a href="../index.php" class="nav-link">Home</a>
                <a href="login.php" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
            </nav>
        </div>
    </header>

    <section class="signup-section">
        <div class="signup-container">
            <div class="signup-card">
                <div class="signup-header">
                    <div class="signup-icon">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <h2>Patient Registration</h2>
                    <p class="signup-subtitle">Create your patient account to access healthcare services</p>
                    <div class="patient-badge">
                        <i class="fas fa-user-check"></i> Patient Account
                    </div>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form class="signup-form" method="POST" onsubmit="return validateForm()">
                    <div class="form-row">
                        <div class="form-group">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="full_name" class="form-input" placeholder="Full Name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <i class="fas fa-at input-icon"></i>
                            <input type="email" name="email" class="form-input" placeholder="Email Address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel" name="phone" class="form-input" placeholder="Phone Number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <i class="fas fa-user-tag input-icon"></i>
                            <input type="text" name="username" class="form-input" placeholder="Username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <i class="fas fa-venus-mars input-icon"></i>
                            <select name="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <div class="password-container">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="password" class="form-input" placeholder="Password" required>
                                <i class="fas fa-eye toggle-password" data-target="password"></i>
                            </div>
                            <div class="password-strength-container">
                                <div class="strength-bars">
                                    <div class="strength-bar"></div>
                                    <div class="strength-bar"></div>
                                    <div class="strength-bar"></div>
                                    <div class="strength-bar"></div>
                                </div>
                                <div class="password-strength">Password Strength</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <div class="password-container">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="confirm_password" class="form-input" placeholder="Confirm Password" required>
                                <i class="fas fa-eye toggle-password" data-target="confirm_password"></i>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="signup-btn">
                        <i class="fas fa-user-plus"></i>
                        Create Patient Account
                    </button>
                </form>
                
                <div class="signup-footer">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                    <p style="margin-top: 0.5rem; font-size: 0.85rem; color: #888;">
                        <i class="fas fa-info-circle"></i> Healthcare professionals: Contact administrator for account creation
                    </p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 SwasthyaTrack. All Rights Reserved.</p>
    </footer>
</body>
</html>