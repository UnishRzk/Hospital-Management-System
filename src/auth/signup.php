<?php
include("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $gender   = $_POST['gender'];
    $phone    = trim($_POST['phone']);

    // Insert into users
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'patient')");
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Insert into patients
        $stmt2 = $conn->prepare("INSERT INTO patients (user_id, name, email, gender, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param("issss", $user_id, $name, $email, $gender, $phone);
        $stmt2->execute();

        echo "Signup successful. <a href='login.php'>Login here</a>";
    } else {
        echo "Error: Username might already exist.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Signup</title>
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

        /* Signup Form Section */
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
            max-width: 420px;
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
        .login-card input,
        .login-card select {
            padding: 0.8rem 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .login-card input:focus,
        .login-card select:focus {
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
            <a href="login.php" class="btn-login">Login</a>
        </nav>
    </header>

    <section class="login-section">
        <div class="login-card">
            <h2>Signup</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <select name="gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="others">Others</option>
                </select>
                <input type="tel" name="phone" placeholder="Phone" required>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="login-btn">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 SwasthyaTrack. All Rights Reserved.</p>
    </footer>
</body>
</html>
