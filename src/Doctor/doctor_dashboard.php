<?php
// Start the session
session_start();

// Connect to the database
include("../config/db.php");

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    // Redirect non-admins to login page
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users — Admin Panel</title>
<style>
  /* --- Basic Styling --- */
  * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
  body { display: flex; height: 100vh; background: #f9f9fb; }

  /* Sidebar */
  .sidebar { width: 250px; background: #015eac; color: #fff; padding: 20px 0; }
  .sidebar h2 { text-align: center; margin-bottom: 30px; }
  .sidebar a { display: block; padding: 12px 20px; color: #fff; text-decoration: none; }
  .sidebar a:hover, .sidebar a.active { background: #004d91; border-left: 4px solid #fff; }

  /* Main Content */
  .main { flex: 1; padding: 20px; overflow-y: auto; }

  /* Topbar */
  .topbar { background: #fff; padding: 15px 20px; margin-bottom: 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
  .topbar h1 { color: #015eac; font-size: 1.6rem; }

  /* Filter */
  .filter-bar form { display: flex; gap: 10px; }
  .filter-bar input, .filter-bar select, .filter-bar button { padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; }
  .filter-bar button { background: #015eac; color: #fff; border: none; cursor: pointer; }

  /* Table */
  .table-container { max-height: 500px; overflow-y: auto; border: 1px solid #ddd; }
  table { border-collapse: collapse; width: 100%; background: #fff; }
  th, td { padding: 12px 15px; border-bottom: 1px solid #ddd; }
  thead th { background: #f0f0f0; position: sticky; top: 0; }
  .actions a { margin-right: 10px; text-decoration: none; color: #015eac; }
  .actions a.delete { color: #f31026; }
</style>
<script>
// Function to confirm delete
function confirmDelete(id) {
    var confirmBox = confirm("Are you sure you want to delete this user?");
    if (confirmBox) {
        window.location.href = "?delete_id=" + id;
    }
}
</script>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Doctor Panel</h2>
  <a href="#">Dashboard</a>
  <a href="manage_appointment.php" class="active">Manage Appointments</a>
  <a href="../auth/logout.php">Logout</a>
</div>

<!-- Main -->
<div class="main">

  <!-- Topbar -->
  <div class="topbar">
    <h1>Appointment Management</h1>
    <div class="filter-bar">
      <form method="get">
        <input type="text" name="search" placeholder="Search by Full Name" >
        
        <select name="status">
          <option value="">Status</option>
          
        </select>

        <button type="submit">Filter</button>
      </form>
    </div>
  </div>

  <!-- User Table -->
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Gender</th>
          <th>Message</th>
          <th>Status</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>

</div>
</body>
</html>
