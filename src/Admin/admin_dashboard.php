<?php
session_start();
include("../config/db.php"); 

// Role check
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     die("Access denied. Only admins can view this page.");
// }

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Query counts
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$totalPatients = $conn->query("SELECT COUNT(*) AS total FROM patients")->fetch_assoc()['total'];
$totalDoctors = $conn->query("SELECT COUNT(*) AS total FROM doctors")->fetch_assoc()['total'];
$totalNurses = $conn->query("SELECT COUNT(*) AS total FROM nurses")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <style>
    /* Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Roboto','Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      display: flex;
      height: 100vh;
      color: #000;
      background: #f9f9fb;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background: #015eac;
      color: #fff;
      backdrop-filter: blur(10px);
      box-shadow: 2px 0 8px rgba(0,0,0,0.05);
      display: flex;
      flex-direction: column;
      padding: 20px 0;
      transition: transform 0.3s ease;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 30px;
      font-size: 1.5rem;
      font-weight: bold;
      color: #fff;
    }

    .sidebar a {
      display: block;
      padding: 12px 20px;
      color: #fff;
      text-decoration: none;
      font-weight: 500;
      transition: 0.3s;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background: #004d91;
      border-left: 4px solid #fff;
    }

    /* Main content */
    .main {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
    }

    .topbar {
      background: #fff;
      padding: 15px 20px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .topbar h1 {
      color: #015eac;
      font-size: 1.6rem;
    }

    .topbar input {
      padding: 8px 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      transition: border 0.3s;
    }

    .topbar input:focus {
      border: 1px solid #015eac;
      outline: none;
    }

    .menu-toggle {
      display: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #015eac;
    }

    /* Cards */
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
    }

    .card {
      background: #fff;
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.06);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-6px);
      box-shadow: 0 10px 24px rgba(0,0,0,0.12);
    }

    .card h3 {
      margin-bottom: 10px;
      font-size: 1.1rem;
      color: #015eac;
    }

    .card p {
      font-size: 1.4rem;
      font-weight: bold;
      color: #000;
    }

    @media (max-width: 992px) {
      body {
        flex-direction: column;
      }

      .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        transform: translateX(-100%);
        z-index: 1000;
      }

      .sidebar.show {
        transform: translateX(0);
      }

      .main {
        margin-left: 0;
        padding: 15px;
      }

      .menu-toggle {
        display: block;
        margin-right: 15px;
      }

      .topbar {
        justify-content: flex-start;
        gap: 15px;
      }

      .topbar h1 {
        font-size: 1.3rem;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php" class="active">Dashboard</a>
    <a href="create_user.php">Add Users</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_appointments.php">Manage Appointments</a>
    <a href="manage_beds.php">Manage Beds</a>
    <a href="../auth/logout.php">Logout</a>
  </div>

  <!-- Main content -->
  <div class="main">
    <div class="topbar">
      <span class="menu-toggle" onclick="toggleSidebar()">☰</span>
      <h1>Dashboard</h1>
      <input type="text" placeholder="Search...">
    </div>

    <div class="cards">
      <div class="card">
        <h3>Total Users</h3>
        <p><?php echo $totalUsers; ?></p>
      </div>
      <div class="card">
        <h3>Patients</h3>
        <p><?php echo $totalPatients; ?></p>
      </div>
      <div class="card">
        <h3>Doctors</h3>
        <p><?php echo $totalDoctors; ?></p>
      </div>
      <div class="card">
        <h3>Nurses</h3>
        <p><?php echo $totalNurses; ?></p>
      </div>
    </div>
  </div>

  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("show");
    }
  </script>
</body>
</html>
