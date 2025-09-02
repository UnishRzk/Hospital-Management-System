<?php
session_start();
include("../config/db.php");

// Only allow admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); // redirect instead of die()
    exit();
}

// Handle delete
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: manage_users.php");
    exit();
}

// Handle search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role   = isset($_GET['role']) ? trim($_GET['role']) : '';

$sql = "SELECT u.user_id, u.username, u.role, u.created_at,
        COALESCE(d.name, p.name, n.name, a.name) AS full_name,
        COALESCE(d.email, p.email, n.email, a.email) AS email
        FROM users u
        LEFT JOIN doctors d ON u.user_id = d.user_id
        LEFT JOIN patients p ON u.user_id = p.user_id
        LEFT JOIN nurses n ON u.user_id = n.user_id
        LEFT JOIN admins a ON u.user_id = a.user_id
        WHERE 1=1";

$params = [];
$types  = "";

// Search filter
if ($search !== '') {
    $sql .= " AND (d.name LIKE ? OR p.name LIKE ? OR n.name LIKE ? OR a.name LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like]);
    $types .= "ssss";
}

// Role filter
if ($role !== '') {
    $sql .= " AND u.role = ?";
    $params[] = $role;
    $types .= "s";
}

$sql .= " ORDER BY u.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users — Admin Panel</title>
<style>
  * {
    margin: 0; padding: 0; box-sizing: border-box;
    font-family: 'Roboto','Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  body {
    display: flex; height: 100vh;
    color: #000; background: #f9f9fb;
  }

  /* Sidebar */
  .sidebar {
    width: 250px; background: #015eac; color: #fff;
    display: flex; flex-direction: column;
    padding: 20px 0; transition: transform 0.3s ease;
  }
  .sidebar h2 {
    text-align: center; margin-bottom: 30px;
    font-size: 1.5rem; font-weight: bold;
  }
  .sidebar a {
    display: block; padding: 12px 20px;
    color: #fff; text-decoration: none; font-weight: 500;
    transition: 0.3s;
  }
  .sidebar a:hover,
  .sidebar a.active {
    background: #004d91; border-left: 4px solid #fff;
  }

  /* Main */
  .main {
    flex: 1; padding: 20px; overflow-y: auto;
  }

  /* Topbar with filter */
  .topbar {
    background: #fff; padding: 15px 20px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
    display: flex; justify-content: space-between; align-items: center;
  }
  .topbar .left {
    display: flex; align-items: center; gap: 15px;
  }
  .topbar h1 {
    color: #015eac; font-size: 1.6rem;
  }
  .menu-toggle {
    display: none; font-size: 1.5rem; cursor: pointer; color: #015eac;
  }

  .filter-bar form {
    display: flex; gap: 10px;
  }
  .filter-bar input,
  .filter-bar select,
  .filter-bar button {
    padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px;
    font-size: 14px;
  }
  .filter-bar button {
    background: #015eac; color: #fff; border: none; cursor: pointer;
  }

  /* Table */
  .table-container {
    max-height: 500px; overflow-y: auto; border: 1px solid #ddd;
  }
  table {
    border-collapse: collapse; width: 100%; background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  }
  thead th {
    position: sticky; top: 0; background: #f0f0f0;
    font-weight: 600; z-index: 1;
  }
  th, td {
    padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd;
  }
  .actions a { margin-right: 10px; text-decoration: none; color: #015eac; }
  .actions a.delete { color: #f31026; }

  /* Responsive */
  @media (max-width: 992px) {
    body { flex-direction: column; }
    .sidebar {
      position: fixed; top: 0; left: 0; height: 100%;
      transform: translateX(-100%); z-index: 1000;
    }
    .sidebar.show { transform: translateX(0); }
    .main { margin-left: 0; padding: 15px; }
    .menu-toggle { display: block; margin-right: 15px; }
    .topbar { flex-direction: column; gap: 15px; align-items: flex-start; }
    .topbar h1 { font-size: 1.3rem; }
  }
</style>
<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("show");
}
function confirmDelete(id) {
  if (confirm("Are you sure you want to delete this user?")) {
    window.location.href = "?delete_id=" + id;
  }
}
</script>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <h2>Admin Panel</h2>
  <a href="../dashboard/admin_dashboard.php">Dashboard</a>
  <a href="../auth/create_user.php">Add Users</a>
  <a href="manage_users.php" class="active">Manage Users</a>
  <a href="../auth/logout.php">Logout</a>
</div>

<!-- Main -->
<div class="main">

  <!-- Topbar + Filter -->
  <div class="topbar">
    <div class="left">
      <span class="menu-toggle" onclick="toggleSidebar()">☰</span>
      <h1>User Management</h1>
    </div>
    <div class="filter-bar">
      <form method="get">
        <input type="text" name="search" placeholder="Search by Full Name" value="<?= htmlspecialchars($search) ?>">
        <select name="role">
          <option value="">All Roles</option>
          <option value="doctor" <?= $role==="doctor" ? "selected" : "" ?>>Doctor</option>
          <option value="patient" <?= $role==="patient" ? "selected" : "" ?>>Patient</option>
          <option value="nurse" <?= $role==="nurse" ? "selected" : "" ?>>Nurse</option>
          <option value="admin" <?= $role==="admin" ? "selected" : "" ?>>Admin</option>
        </select>
        <button type="submit">Filter</button>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Email</th>
          <th>Username</th>
          <th>Role</th>
          <th>Joined Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['full_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars(ucfirst($row['role'])) ?></td>
            <td><?= date("F j, Y", strtotime($row['created_at'])) ?></td>
            <td class="actions">
              <a href="edit_user.php?id=<?= $row['user_id'] ?>">✏️</a>
              <a href="javascript:void(0);" onclick="confirmDelete(<?= $row['user_id'] ?>)" class="delete">🗑️</a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6">No users found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
