<?php
session_start();
include("../config/db.php");

// Restrict access to admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// DELETE BED
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM beds WHERE bed_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: manage_beds.php");
    exit();
}

// FILTER PARAMETERS
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$typeFilter = $_GET['type'] ?? '';

//  BASE QUERY 
$sql = "SELECT * FROM beds WHERE 1=1";
$params = [];
$types = "";

// Dynamic Filters
if ($search !== '') {
    $sql .= " AND (patient_name LIKE ? OR reason_for_admission LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if ($statusFilter !== '') {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if ($typeFilter !== '') {
    $sql .= " AND type = ?";
    $params[] = $typeFilter;
    $types .= "s";
}

$sql .= " ORDER BY bed_id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$beds = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Beds | Admin Panel</title>
<style>
  * { 
    margin: 0; 
    padding: 0; 
    box-sizing: border-box; 
    font-family: Arial, sans-serif; 
  }

  body {
     display: flex; 
     height: 100vh; 
     background: #f9f9fb; 
    }

  /* Sidebar */
  .sidebar { 
    width: 250px; 
    background: #015eac; 
    color: #fff; 
    padding: 20px 0; 
    display: flex; 
    flex-direction: column; 
    align-items: center; 
  }

  .sidebar h2 { 
    margin-bottom: 30px; 
  }

  .sidebar a { 
    display: block; 
    width: 100%; 
    padding: 12px 20px; 
    color: #fff; 
    text-decoration: none; 
  }

  .sidebar a:hover, .sidebar a.active { 
    background: #004d91;
     border-left: 4px solid #fff; 
    }

  /* Main */
  .main { 
    flex: 1; 
    padding: 20px; 
    overflow-y: auto; 
  }

  /* Topbar */
  .topbar {
    background: #fff; 
    padding: 15px 20px; 
    margin-bottom: 20px;
    border-radius: 12px; 
    display: flex; 
    justify-content: space-between; 
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  }

  .topbar h1 { 
    color: #015eac; 
    font-size: 1.6rem; 
  }

  /* Filter */
  .filter-bar form { 
    display: flex; 
    gap: 10px; 
    flex-wrap: wrap; 
  }

  .filter-bar input, .filter-bar select, .filter-bar button {
    padding: 8px 10px; 
    border: 1px solid #ccc; 
    border-radius: 4px;
  }

  .filter-bar button {
    background: #015eac; 
    color: #fff; 
    border: none; 
    cursor: pointer; 
    transition: 0.3s;
  }

  .filter-bar button:hover { 
    background: #004d91; 
  }

  /* Table */
  .table-container { 
    max-height: 500px; 
    overflow-y: auto; 
    border-radius: 8px; 
  }
  
  table { 
    border-collapse: collapse; 
    width: 100%; 
    background: #fff; 
    border-radius: 8px; 
  }

  th, td { 
    padding: 12px 15px; 
    border-bottom: 1px solid #ddd; 
    text-align: left; 
  }

  thead th {
     background: #f0f0f0; 
     position: sticky; 
     top: 0; 
    }

  /* Status Label */
.status {
  padding: 6px 10px;
  border-radius: 4px;
  color: #fff;
  font-weight: bold;
  text-align: center;
  display: inline-block;
  min-width: 90px;
}

/* Updated Colors */
.status.Empty {
  background-color: #27ae60; /* Green */
}

.status.Reserved {
  background-color: #f1c40f; /* Yellow */
  color: #000;
}

.status.Occupied {
  background-color: #e74c3c; /* Red */
}

.status.Out.of.Order {
  background-color: #3498db; /* Blue */
}

  /* Actions */
  .actions a {
    margin-right: 10px;
    text-decoration: none;
    font-size: 1.2rem;
  }
  .actions a.edit { 
    color: #015eac; 
  }

  .actions a.delete { 
    color: #f31026; 
    }
    
</style>

<script>
function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this bed record?")) {
        window.location.href = "?delete_id=" + id;
    }
}
</script>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Admin Panel</h2>
      <a href="admin_dashboard.php" >Dashboard</a>
    <a href="create_user.php">Add Users</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_appointments.php">Manage Appointments</a>
    <a href="manage_beds.php" class="active">Manage Beds</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<!-- Main -->
<div class="main">
  <div class="topbar">
    <h1>Bed Management</h1>
    <div class="filter-bar">
      <form method="get">
        <input type="text" name="search" placeholder="Search by Patient or Reason" value="<?= htmlspecialchars($search) ?>">
        <select name="status">
          <option value="">Status</option>
          <option value="Empty" <?= $statusFilter == 'Empty' ? 'selected' : '' ?>>Empty</option>
          <option value="Reserved" <?= $statusFilter == 'Reserved' ? 'selected' : '' ?>>Reserved</option>
          <option value="Occupied" <?= $statusFilter == 'Occupied' ? 'selected' : '' ?>>Occupied</option>
          <option value="Out of Order" <?= $statusFilter == 'Out of Order' ? 'selected' : '' ?>>Out of Order</option>
        </select>
        <select name="type">
          <option value="">Type</option>
          <option value="General" <?= $typeFilter == 'General' ? 'selected' : '' ?>>General</option>
          <option value="Semi-Private" <?= $typeFilter == 'Semi-Private' ? 'selected' : '' ?>>Semi-Private</option>
          <option value="Private" <?= $typeFilter == 'Private' ? 'selected' : '' ?>>Private</option>
        </select>
        <button type="submit">Filter</button>
      </form>
    </div>
  </div>

  <a href="add_bed.php" style="background:#015eac;color:#fff;padding:10px 15px;border-radius:5px;text-decoration:none;">+ Add Bed</a>
  <br><br>

  <!-- Bed Table -->
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Patient Name</th>
          <th>Gender</th>
          <th>Reason</th>
          <th>Status</th>
          <th>Date</th>
          <th>Bed Type</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($beds->num_rows > 0): ?>
          <?php while ($row = $beds->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['patient_name'] ?? '-') ?></td>
              <td><?= htmlspecialchars($row['gender'] ?? '-') ?></td>
              <td><?= htmlspecialchars($row['reason_for_admission'] ?? '-') ?></td>
              <td><span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
              <td><?= htmlspecialchars($row['reserved_date'] ?? '-') ?></td>
              <td><?= htmlspecialchars($row['type']) ?></td>
              <td class="actions">
                <a href="edit_bed.php?id=<?= $row['bed_id'] ?>" class="edit">✏️</a>
                <a href="javascript:void(0);" onclick="confirmDelete(<?= $row['bed_id'] ?>)" class="delete">🗑️</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7">No bed records found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
