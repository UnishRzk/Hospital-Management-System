<?php
$servername = "mysql"; // service name from docker-compose
$username   = "root";  // same as MYSQL_USER
$password   = "root"; // same as MYSQL_PASSWORD
$database   = "swasthyatrack";   // same as MYSQL_DATABASE

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully to MySQL database '$database'";

