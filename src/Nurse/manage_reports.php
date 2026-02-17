<?php
session_start();
include("../config/db.php");

// Restrict to logged-in nurses only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'nurse') {
    header("Location: ../auth/login.php");
    exit();
}

// FILTER: search by patient name
$search = trim($_GET['search'] ?? '');

// BASE QUERY (uses prepared statements for security)
if ($search !== '') {
    $sql = "SELECT patient_id, user_id, name, email, gender, phone
            FROM patients
            WHERE name LIKE ?
            ORDER BY patient_id DESC";
    $stmt = $conn->prepare($sql);
    $like = "%{$search}%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT patient_id, user_id, name, email, gender, phone
            FROM patients
            ORDER BY patient_id DESC";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Reports | Nurse Panel</title>
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
    align-items: center; 
    flex-wrap: wrap; 
  }

  .filter-bar input[type="text"], .filter-bar button {
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

  /* Button */
  .upload-btn {
    background: #015eac; 
    color: #fff; 
    border: none; 
    border-radius: 4px;
    padding: 8px 14px; 
    text-decoration: none; 
    cursor: pointer; 
    transition: 0.3s;
  }
  
  .upload-btn:hover { 
    background: #004d91; 
  }

  /* Responsive */
  @media (max-width: 768px) {
    .sidebar { width: 200px; }
    table, thead, tbody, th, td, tr { display: block; width: 100%; }
    th { display: none; }
    td { padding: 10px; border-bottom: 1px solid #eee; }
    td::before { content: attr(data-label); font-weight: bold; display: block; margin-bottom: 5px; }
  }
</style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Nurse Panel</h2>
  <a href="nurse_dashboard.php">Manage Appointments</a>
  <a href="manage_reports.php" class="active">Upload Reports</a>
  <a href="../auth/logout.php">Logout</a>
</div>

<!-- Main -->
<div class="main">
  <div class="topbar">
    <h1>Patient Reports</h1>
    <div class="filter-bar">
      <form method="get" action="">
        <input type="text" name="search" placeholder="Search by patient name" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit">Search</button>
        <?php if ($search !== ''): ?>
          <button type="button" onclick="window.location.href='manage_reports.php'">Clear</button>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Email</th>
          <th>Gender</th>
          <th>Phone</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td data-label="Full Name"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td data-label="Email"><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
              <td data-label="Gender"><?= htmlspecialchars($row['gender'], ENT_QUOTES, 'UTF-8') ?></td>
              <td data-label="Phone"><?= htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8') ?></td>
              <td data-label="Action">
                <a href="upload_report.php?user_id=<?= urlencode($row['user_id']) ?>" class="upload-btn">Upload Report</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5">No patients found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
