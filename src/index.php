<?php
$servername = "mysql"; // service name from docker-compose
$username   = "user";  // same as MYSQL_USER
$password   = "password"; // same as MYSQL_PASSWORD
$database   = "testdb";   // same as MYSQL_DATABASE

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "✅ Connected successfully to MySQL database '$database'";

$conn->close();
?>
