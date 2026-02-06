<?php
// Start the session
session_start();

// Connect to the database
include("../config/db.php");

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect non-admins to login page
    header("Location: ../auth/login.php");
    exit();
}

// --------------------- DELETE USER ---------------------
if (isset($_GET['delete_id'])) {
    if (is_numeric($_GET['delete_id'])) {
        $delete_id = (int)$_GET['delete_id'];

        // Prepare the delete query
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();

        // Redirect back after deleting
        header("Location: manage_users.php");
        exit();
    }
}

// --------------------- SEARCH AND FILTER ---------------------
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

$role = "";
if (isset($_GET['role'])) {
    $role = trim($_GET['role']);
}

// Start building the SQL query
$sql = "SELECT u.user_id, u.username, u.role, u.created_at,
        COALESCE(d.name, p.name, n.name, a.name) AS full_name,
        COALESCE(d.email, p.email, n.email, a.email) AS email
        FROM users u
        LEFT JOIN doctors d ON u.user_id = d.user_id
        LEFT JOIN patients p ON u.user_id = p.user_id
        LEFT JOIN nurses n ON u.user_id = n.user_id
        LEFT JOIN admins a ON u.user_id = a.user_id
        WHERE 1=1";

$params = array();
$types = "";

// If search is not empty
if ($search !== "") {
    $sql .= " AND (d.name LIKE ? OR p.name LIKE ? OR n.name LIKE ? OR a.name LIKE ?)";
    $like = "%" . $search . "%";

    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;

    $types .= "ssss";
}

// If role filter is not empty
if ($role !== "") {
    $sql .= " AND u.role = ?";
    $params[] = $role;
    $types .= "s";
}

// Sort by newest first
$sql .= " ORDER BY u.created_at DESC";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters if there are any
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
  <h2>Admin Panel</h2>
      <a href="admin_dashboard.php" >Dashboard</a>
    <a href="create_user.php">Add Users</a>
    <a href="manage_users.php" class="active">Manage Users</a>
    <a href="manage_appointments.php">Manage Appointments</a>
    <a href="manage_beds.php">Manage Beds</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<!-- Main -->
<div class="main">

  <!-- Topbar -->
  <div class="topbar">
    <h1>User Management</h1>
    <div class="filter-bar">
      <form method="get">
        <input type="text" name="search" placeholder="Search by Full Name" value="<?php echo htmlspecialchars($search); ?>">
        
        <select name="role">
          <option value="">All Roles</option>
          <?php
          // Dropdown options with simple if-else
          if ($role === "doctor") {
              echo '<option value="doctor" selected>Doctor</option>';
          } else {
              echo '<option value="doctor">Doctor</option>';
          }

          if ($role === "patient") {
              echo '<option value="patient" selected>Patient</option>';
          } else {
              echo '<option value="patient">Patient</option>';
          }

          if ($role === "nurse") {
              echo '<option value="nurse" selected>Nurse</option>';
          } else {
              echo '<option value="nurse">Nurse</option>';
          }

          if ($role === "admin") {
              echo '<option value="admin" selected>Admin</option>';
          } else {
              echo '<option value="admin">Admin</option>';
          }
          ?>
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
          <th>Email</th>
          <th>Username</th>
          <th>Role</th>
          <th>Joined Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            // Loop through all users
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['full_name'] ?? '-') . "</td>";
                echo "<td>" . htmlspecialchars($row['email'] ?? '-') . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars(ucfirst($row['role'])) . "</td>";
                echo "<td>" . date("F j, Y", strtotime($row['created_at'])) . "</td>";
                echo "<td class='actions'>";
                echo "<a href='edit_user.php?id=" . $row['user_id'] . "'>✏️</a>";
                echo "<a href='javascript:void(0);' onclick='confirmDelete(" . $row['user_id'] . ")' class='delete'>🗑️</a>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            // No users found
            echo "<tr><td colspan='6'>No users found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
