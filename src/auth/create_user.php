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

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        if ($role == 'doctor') {
            $stmt2 = $conn->prepare("INSERT INTO doctors (user_id, name, email, phone) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("isss", $user_id, $name, $email, $phone);
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

        echo "User created successfully.";
    } else {
        echo "Error creating user. Username may already exist.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create User</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="form-container">
    <h2>Create User (Admin)</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="text" name="phone" placeholder="Phone"><br>
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="admin">Admin</option>
            <option value="doctor">Doctor</option>
            <option value="nurse">Nurse</option>
        </select><br>
        <button type="submit">Create User</button>
    </form>
</div>
</body>
</html>
