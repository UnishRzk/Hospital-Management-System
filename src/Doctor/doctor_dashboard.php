<?php
session_start();
include("../config/db.php");

// Restrict access to doctors only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

// Get the logged-in doctor ID
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$doctor_id = $result['doctor_id'] ?? 0;

// Filtering parameters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';

// Query appointments (includes patient user_id)
$sql = "SELECT appointment_id, patient_name, gender, message, status, appointment_date, user_id
        FROM appointments
        WHERE doctor_id = ?";
$params = [$doctor_id];
$types = "i";

if ($search !== '') {
    $sql .= " AND patient_name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if ($statusFilter !== '') {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if ($dateFilter !== '') {
    $sql .= " AND DATE(appointment_date) = ?";
    $params[] = $dateFilter;
    $types .= "s";
}

$sql .= " ORDER BY appointment_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Appointments | Doctor Panel</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
  body { display: flex; height: 100vh; background: #f9f9fb; }

  .sidebar {
    width: 250px; background: #015eac; color: #fff; padding: 20px 0;
    display: flex; flex-direction: column; align-items: center;
  }
  .sidebar h2 { margin-bottom: 30px; }
  .sidebar a {
    display: block; width: 100%; padding: 12px 20px;
    color: #fff; text-decoration: none;
  }
  .sidebar a:hover, .sidebar a.active {
    background: #004d91; border-left: 4px solid #fff;
  }

  .main { flex: 1; padding: 20px; overflow-y: auto; }

  .topbar {
    background: #fff; padding: 15px 20px; margin-bottom: 20px;
    border-radius: 12px; display: flex; justify-content: space-between; align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  }
  .topbar h1 { color: #015eac; font-size: 1.6rem; }

  .filter-bar form { display: flex; gap: 10px; flex-wrap: wrap; }
  .filter-bar input, .filter-bar select, .filter-bar button {
    padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px;
  }
  .filter-bar button {
    background: #015eac; color: #fff; border: none; cursor: pointer;
    transition: 0.3s;
  }
  .filter-bar button:hover { background: #004d91; }

  .table-container { max-height: 500px; overflow-y: auto; border-radius: 8px; }
  table { border-collapse: collapse; width: 100%; background: #fff; border-radius: 8px; }
  th, td { padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; }
  thead th { background: #f0f0f0; position: sticky; top: 0; }

  .btn {
    display: inline-block; padding: 8px 12px; border-radius: 4px;
    background: #015eac; color: #fff; text-decoration: none; font-size: 0.9rem;
    border: none; cursor: pointer; transition: 0.3s;
  }
  .btn:hover { background: #004d91; }

  .status {
    padding: 6px 10px; border-radius: 4px; color: #fff; font-weight: bold;
    text-align: center; display: inline-block; min-width: 90px;
  }
  .status.Booked { background-color: #f5b914; color: #000; }
  .status.Completed { background-color: #2ecc71; }
  .status.Cancelled { background-color: #e74c3c; }
</style>
</head>

<body>

<div class="sidebar">
  <h2>Doctor Panel</h2>
  <a href="#">Dashboard</a>
  <a href="manage_appointment.php" class="active">Manage Appointments</a>
  <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">
  <div class="topbar">
    <h1>Appointment Management</h1>
    <div class="filter-bar">
      <form method="get">
        <input type="text" name="search" placeholder="Search by Full Name" value="<?= htmlspecialchars($search) ?>">
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
          <th>Reports</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($appointments->num_rows > 0): ?>
          <?php while ($row = $appointments->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['patient_name']) ?></td>
              <td><?= htmlspecialchars($row['gender']) ?></td>
              <td><?= htmlspecialchars($row['message']) ?></td>
              <td>
                <?php
                  $status = htmlspecialchars($row['status']);
                  echo "<span class='status {$status}'>$status</span>";
                ?>
              </td>
              <td><?= htmlspecialchars($row['appointment_date']) ?></td>
              <td><a href="viewmore.php?id=<?= $row['appointment_id'] ?>" class="btn">View More</a></td>
              <td><a href="user_report.php?user_id=<?= $row['user_id'] ?>" class="btn">View Report</a></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7">No appointments found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
