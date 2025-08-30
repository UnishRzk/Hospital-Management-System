<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    die("Access denied");
}
include("../includes/dashboard_header.html");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="form-container">
    <h2>Welcome Admin</h2>
    <a href="../auth/create_user.php">Create User</a><br><br>
    <a href="../auth/logout.php">Logout</a>
</div>
</body>
</html>
