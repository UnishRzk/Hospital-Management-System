<?php
session_start();
include("../config/db.php");

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
                header("Location: ../dashboards/patient_dashboard.php");
                break;
            case 'doctor':
                header("Location: ../dashboards/doctor_dashboard.php");
                break;
            case 'nurse':
                header("Location: ../dashboards/nurse_dashboard.php");
                break;
            case 'admin':
                header("Location: ../dashboards/admin_dashboard.php");
                break;
        }
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        /* Reuse your existing theme */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto','Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            line-height: 1.6;
            color: #000000;
            background: #f9f9fb;
        }
        a {
            text-decoration: none;
            color: inherit;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 3%;
            background: #ffffffcc;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .swasthya-color {
            color: #015eac;
        }

        .track-color {
            color: #f31026;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #015eac;
        }
        nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        nav a {
            font-weight: 500;
            color: #000000;
            transition: color 0.3s, transform 0.2s;
        }

        nav a:hover {
            color: #f31026;
            transform: translateY(-2px);
        }

        .nav-img{
            height: 30px;
            width: 40px;
            margin-bottom: 7px;
            vertical-align: middle;
        }

        .btn-login {
            padding: 0.4rem 1rem;
            border: 1px solid #015eac;
            border-radius: 8px;
            color: #015eac;
            transition: 0.3s;
        }
        .btn-login:hover {
            background: #2d6cdf;
            color: #fff;
        }

        /* Login Form Section */
        .login-section {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 81vh;
            padding: 2rem;
            background: linear-gradient(120deg, #e8f0ff, #f9f9fb);
        }
        .login-card {
            background: #fff;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            max-width: 400px;
            width: 100%;
            text-align: center;
            animation: fadeInUp 0.8s ease;
        }
        .login-card h2 {
            margin-bottom: 1.5rem;
            font-size: 28px;
            color: #015eac;
        }
        .login-card form {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }
        .login-card input {
            padding: 0.8rem 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .login-card input:focus {
            border-color: #015eac;
            outline: none;
        }
        .login-btn {
            background: #015eac;
            color: #fff;
            border: none;
            padding: 0.9rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .login-btn:hover {
            background: #1f4fbf;
        }
        .login-card p {
            margin-top: 1rem;
            font-size: 0.95rem;
        }
        .login-card a {
            color: #f31026;
            font-weight: 500;
        }

        footer {
            background: #015eac;
            color: #fff;
            text-align: center;
            padding: 1rem;
            margin-top: 0rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {opacity: 0; transform: translateY(30px);}
            to {opacity: 1; transform: translateY(0);}
        }

        @media (max-width: 500px) {
            .login-card {
                padding: 2rem 1.5rem;
            }
            .login-card h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <header>
    <div class="logo">
        <a href="../index.php">
            <img class="nav-img" src="../images/nav-logo.png" alt="">
            <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
        </a>
    </div>
    <nav>
        <a href="signup.php" class="btn-login">Register</a>
    </nav>
</header>

<section class="login-section">
    <div class="login-card">
        <h2>Login</h2>
            <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form  method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="login-btn">Sign In</button>
        </form>
        <p>Don’t have an account? <a href="signup.php">Register here</a></p>
    </div>
</section>

<footer>
    <p>&copy; 2025 SwasthyaTrack. All Rights Reserved.</p>
</footer>

<!-- <div class="form-container">
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
</div> -->
</body>
</html>
