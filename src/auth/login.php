<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

session_start();

// Force no caching — prevents browser from loading old pages

// If user is already logged in, redirect them away from login page
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password, $role);
    $stmt->fetch();
    $stmt->close();

    if ($user_id && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;

        switch ($role) {
            case 'patient':
                header("Location: ../Patient/patient_dashboard.php");
                break;
            case 'doctor':
                header("Location: ../Doctor/doctor_dashboard.php");
                break;
            case 'nurse':
                header("Location: ../Nurse/nurse_dashboard.php");
                break;
            case 'admin':
                header("Location: ../Admin/admin_dashboard.php");
                break;
        }
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SwasthyaTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script type="text/javascript">
    // Prevent page caching and back navigation to login
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
        
        // Clear any stored form data
        const forms = document.getElementsByTagName('form');
        for (let form of forms) {
            form.reset();
        }
        
        // Add focus effects to form inputs
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
        
        // Toggle password visibility
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = document.querySelector('input[name="password"]');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    };
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

        .btn-register {
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

        .btn-register:hover {
            background: var(--primary-blue);
            color: var(--text-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        /* Login Section */
        .login-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: var(--gradient-light);
            position: relative;
            overflow: hidden;
        }

        .login-section::before {
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

        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 1000px;
            width: 100%;
            z-index: 1;
            position: relative;
        }

        .login-card {
            background: var(--background-white);
            padding: 3rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-heavy);
            text-align: center;
            animation: fadeInUp 0.8s ease;
            position: relative;
            width: 100%;
            max-width: 450px;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary-blue);
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .login-header {
            margin-bottom: 2rem;
        }

        .login-icon {
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

        .login-card h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            color: var(--primary-blue);
        }

        .login-subtitle {
            color: #666;
            font-size: 0.95rem;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            position: relative;
            text-align: left;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background: #f9f9f9;
        }

        .form-input:focus {
            border-color: var(--primary-blue);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(1, 94, 172, 0.1);
            outline: none;
        }

        .form-group.focused .form-input {
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

        .login-btn {
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

        .login-btn:hover {
            background: #1f4fbf;
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .login-btn:active {
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

        .login-footer {
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #666;
        }

        .login-footer a {
            color: var(--primary-blue);
            font-weight: 500;
            transition: var(--transition);
        }

        .login-footer a:hover {
            color: var(--primary-red);
        }

        /* Role Highlights */
        .role-highlights {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .role-badge {
            background: rgba(1, 94, 172, 0.1);
            color: var(--primary-blue);
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .role-badge.patient {
            background: rgba(1, 94, 172, 0.1);
            color: var(--primary-blue);
        }

        .role-badge.doctor {
            background: rgba(243, 16, 38, 0.1);
            color: var(--primary-red);
        }

        .role-badge.staff {
            background: rgba(1, 94, 172, 0.1);
            color: var(--primary-blue);
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
            .login-card {
                padding: 2rem 1.5rem;
            }
            
            .role-highlights {
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 1.5rem;
            }
            
            .login-card h2 {
                font-size: 1.5rem;
            }
            
            .form-input {
                padding: 0.9rem 0.9rem 0.9rem 2.8rem;
            }
            
            .input-icon {
                left: 0.9rem;
            }
            
            .role-badge {
                font-size: 0.75rem;
                padding: 0.3rem 0.6rem;
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
                <a href="signup.php" class="btn-register">
                    <i class="fas fa-user-plus"></i>
                    Register
                </a>
            </nav>
        </div>
    </header>

    <section class="login-section">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <div class="login-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h2>Welcome Back</h2>
                    <p class="login-subtitle">Sign in to access your healthcare dashboard</p>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form class="login-form" method="POST">
                    <div class="form-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="username" class="form-input" placeholder="Username" required>
                    </div>
                    
                    <div class="form-group">
                        <div class="password-container">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" class="form-input" placeholder="Password" required>
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>
                
                <div class="role-highlights">
                    <span class="role-badge patient">Patients</span>
                    <span class="role-badge doctor">Doctors</span>
                    <span class="role-badge staff">Medical Staff</span>
                </div>
                
                <div class="login-footer">
                    <p>Don't have an account? <a href="signup.php">Register here</a></p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2026 SwasthyaTrack. All Rights Reserved.</p>
    </footer>
</body>
</html>