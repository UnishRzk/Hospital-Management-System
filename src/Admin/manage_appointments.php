<?php
session_start();
include("../config/db.php");

// Restrict access to admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// --------------------- DELETE APPOINTMENT ---------------------
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: manage_appointment.php");
    exit();
}

// --------------------- FILTER PARAMETERS ---------------------
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';

// --------------------- BASE QUERY ---------------------
$sql = "SELECT a.appointment_id, a.patient_name, a.gender, a.message, a.status, 
               a.appointment_date, d.name AS doctor_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.doctor_id
        WHERE 1=1";

$params = [];
$types = "";

// --------------------- DYNAMIC FILTERS ---------------------
if ($search !== '') {
    $sql .= " AND (a.patient_name LIKE ? OR d.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if ($statusFilter !== '') {
    $sql .= " AND a.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if ($dateFilter !== '') {
    $sql .= " AND DATE(a.appointment_date) = ?";
    $params[] = $dateFilter;
    $types .= "s";
}

$sql .= " ORDER BY a.appointment_date DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$appointments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Appointments | Admin Panel</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
  body { display: flex; height: 100vh; background: #f9f9fb; }

  /* Sidebar */
  .sidebar { width: 250px; background: #015eac; color: #fff; padding: 20px 0; display: flex; flex-direction: column; align-items: center; }
  .sidebar h2 { margin-bottom: 30px; }
  .sidebar a { display: block; width: 100%; padding: 12px 20px; color: #fff; text-decoration: none; }
  .sidebar a:hover, .sidebar a.active { background: #004d91; border-left: 4px solid #fff; }

  /* Main */
  .main { flex: 1; padding: 20px; overflow-y: auto; }

  /* Topbar */
  .topbar {
    background: #fff; padding: 15px 20px; margin-bottom: 20px;
    border-radius: 12px; display: flex; justify-content: space-between; align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  }
  .topbar h1 { color: #015eac; font-size: 1.6rem; }

  /* Filter */
  .filter-bar form { display: flex; gap: 10px; flex-wrap: wrap; }
  .filter-bar input, .filter-bar select, .filter-bar button {
    padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px;
  }
  .filter-bar button {
    background: #015eac; color: #fff; border: none; cursor: pointer; transition: 0.3s;
  }
  .filter-bar button:hover { background: #004d91; }

  /* Table */
  .table-container { max-height: 500px; overflow-y: auto; border-radius: 8px; }
  table { border-collapse: collapse; width: 100%; background: #fff; border-radius: 8px; }
  th, td { padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; }
  thead th { background: #f0f0f0; position: sticky; top: 0; }

  /* Status Label */
  .status { padding: 6px 10px; border-radius: 4px; color: #fff; font-weight: bold; text-align: center; display: inline-block; min-width: 90px; }
  .status.Booked { background-color: #f5b914; color: #000; }
  .status.Completed { background-color: #2ecc71; }
  .status.Cancelled { background-color: #e74c3c; }

  /* Actions */
  .actions a {
    margin-right: 10px;
    text-decoration: none;
    font-size: 1.2rem;
  }
  .actions a.edit { color: #015eac; }
  .actions a.delete { color: #f31026; }
</style>

<script>
function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this appointment?")) {
        window.location.href = "?delete_id=" + id;
    }
}
</script>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Admin Panel</h2>
      <a href="admin_dashboard.php">Dashboard</a>
    <a href="create_user.php">Add Users</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_appointments.php" class="active">Manage Appointments</a>
    <a href="manage_beds.php">Manage Beds</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<!-- Main -->
<div class="main">
  <div class="topbar">
    <h1>Appointment Management</h1>
    <div class="filter-bar">
      <form method="get">
        <input type="text" name="search" placeholder="Search by Patient or Doctor Name" value="<?= htmlspecialchars($search) ?>">
        <select name="status">
          <option value="">Status</option>
          <option value="Booked" <?= $statusFilter == 'Booked' ? 'selected' : '' ?>>Booked</option>
          <option value="Completed" <?= $statusFilter == 'Completed' ? 'selected' : '' ?>>Completed</option>
          <option value="Cancelled" <?= $statusFilter == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <input type="date" name="date" value="<?= htmlspecialchars($dateFilter) ?>">
        <button type="submit">Filter</button>
      </form>
    </div>
  </div>

  <!-- Appointment Table -->
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Gender</th>
          <th>Message</th>
          <th>Status</th>
          <th>Date</th>
          <th>Doctor Name</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($appointments->num_rows > 0): ?>
          <?php while ($row = $appointments->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['patient_name']) ?></td>
              <td><?= htmlspecialchars($row['gender']) ?></td>
              <td><?= htmlspecialchars($row['message']) ?></td>
              <td><span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
              <td><?= htmlspecialchars($row['appointment_date']) ?></td>
              <td><?= htmlspecialchars($row['doctor_name']) ?></td>
              <td class="actions">
                <a href="edit_appointment.php?id=<?= $row['appointment_id'] ?>" class="edit">✏️</a>
                <a href="javascript:void(0);" onclick="confirmDelete(<?= $row['appointment_id'] ?>)" class="delete">🗑️</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7">No appointments found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
