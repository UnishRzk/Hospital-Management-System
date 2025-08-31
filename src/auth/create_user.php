<?php
session_start();
include("../config/db.php");

if ($_SESSION['role'] != 'admin') {
    die("Access denied");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);


    $department      = !empty($_POST['department']) ? trim($_POST['department']) : null;
    $designation     = !empty($_POST['designation']) ? trim($_POST['designation']) : null;
    $council_number  = !empty($_POST['council_number']) ? trim($_POST['council_number']) : null;

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        if ($role == 'doctor') {
            $stmt2 = $conn->prepare("INSERT INTO doctors 
                (user_id, name, email, department, designation, council_number, phone) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("issssss", $user_id, $name, $email, $department, $designation, $council_number, $phone);
            $stmt2->execute();

        } elseif ($role == 'nurse') {
            $stmt2 = $conn->prepare("INSERT INTO nurses (user_id, name, email, phone) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("isss", $user_id, $name, $email, $phone);
            $stmt2->execute();

        } elseif ($role == 'admin') {
            $stmt2 = $conn->prepare("INSERT INTO admins (user_id, name, email) VALUES (?, ?, ?)");
            $stmt2->bind_param("iss", $user_id, $name, $email);
            $stmt2->execute();
        }

        $success = "User created successfully.";
    } else {
        $error = "Error creating user. Username may already exist.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create User | SwasthyaTrack</title>
    <style>
        /* ===== Base ===== */
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
        .swasthya-color { color: #015eac; }
        .track-color { color: #f31026; }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #015eac;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .nav-img {
            height: 30px;
            width: 40px;
            margin-bottom: 5px;
        }

        /* ===== Create User Form Section ===== */
        .form-section {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 81vh;
            padding: 2rem;
            background: linear-gradient(120deg, #e8f0ff, #f9f9fb);
        }
        .form-card {
            background: #fff;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            max-width: 500px;
            width: 100%;
            animation: fadeInUp 0.8s ease;
        }
        .form-card h2 {
            margin-bottom: 1.5rem;
            font-size: 26px;
            color: #015eac;
            text-align: center;
        }
        .form-card form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .form-card input,
        .form-card select {
            padding: 0.8rem 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-card input:focus,
        .form-card select:focus {
            border-color: #015eac;
            outline: none;
        }

        .doctor-fields {
            display: flex;
            flex-direction: column;
            gap: 1rem; /* adds spacing between inputs */
            margin-top: 0.5rem;
        }


        .create-btn {
            background: #015eac;
            color: #fff;
            border: none;
            padding: 0.9rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .create-btn:hover {
            background: #1f4fbf;
        }
        .message {
            text-align: center;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        .success { color: green; }
        .error { color: red; }

        footer {
            background: #015eac;
            color: #fff;
            text-align: center;
            padding: 1rem;
        }


        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from {opacity: 0; transform: translateY(30px);}
            to {opacity: 1; transform: translateY(0);}
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
</header>

<section class="form-section">
    <div class="form-card">
        <h2>Create User (Admin)</h2>

        <?php if (!empty($success)) echo "<p class='message success'>$success</p>"; ?>
        <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Phone">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>

            <select name="role" id="roleSelect" required onchange="toggleDoctorFields()">
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="doctor">Doctor</option>
                <option value="nurse">Nurse</option>
            </select>

            <!-- Doctor-specific fields -->
        <div id="doctorFields" class="doctor-fields">
            <input type="text" name="department" placeholder="Department">
            <input type="text" name="designation" placeholder="Designation">
            <input type="text" name="council_number" placeholder="Council Number">
        </div>


            <button type="submit" class="create-btn">Create User</button>
        </form>
    </div>
</section>

<footer>
    <p>&copy; 2025 SwasthyaTrack. All Rights Reserved.</p>
</footer>
</body>
    <script>
        function toggleDoctorFields() {
            const role = document.getElementById("roleSelect").value;
            document.getElementById("doctorFields").style.display = (role === "doctor") ? "flex" : "none";
        }
    </script>
</html>
