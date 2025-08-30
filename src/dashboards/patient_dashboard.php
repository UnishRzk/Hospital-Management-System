<?php
session_start();
if ($_SESSION['role'] != 'patient') {
    die("Access denied");
}
include("../includes/dashboard_header.html");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <H1>This is Patient Dashboard</H1>
</body>
</html>