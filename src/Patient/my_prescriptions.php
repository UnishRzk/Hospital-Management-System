<?php
session_start();
include("../config/db.php");

// Restrict to logged-in patients
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// FETCH COMPLETED APPOINTMENTS
$search = $_GET['search'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$sortOrder = $_GET['sort'] ?? 'desc';

$sql = "SELECT a.appointment_id, a.patient_name, a.gender, a.address, a.message,
               a.status, a.appointment_date, d.name AS doctor_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.doctor_id
        WHERE a.user_id = ?
          AND a.status = 'Completed'";

$params = [$user_id];
$types = "i";

if ($search !== '') {
    $sql .= " AND (a.patient_name LIKE ? OR d.name LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if ($dateFilter !== '') {
    $sql .= " AND DATE(a.appointment_date) = ?";
    $params[] = $dateFilter;
    $types .= "s";
}

$orderSQL = ($sortOrder === 'asc') ? "ASC" : "DESC";
$sql .= " ORDER BY a.appointment_date $orderSQL";

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
<title>My Prescriptions — SwasthyaTrack</title>
<link rel="stylesheet" href="../css/patient-dashboard.css">
<style>
main {
  padding: 40px 3%;
  background: linear-gradient(180deg, #f1f7ff, #eef6ff);
  min-height: 100vh;
}
.table-container {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(6, 18, 32, 0.08);
  overflow: hidden;
}
table { width: 100%;
 border-collapse: collapse;
 }
thead { 
  background-color: #015eac; 
  color: #fff; 
}
th, td {
  padding: 14px 18px;
  text-align: left;
  font-size: 0.95rem;
  border-bottom: 1px solid #eee;
}
tbody tr:hover { 
  background-color: #f9fbff; 
  transition: 0.3s ease; 
}
.status {
  padding: 6px 10px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.85rem;
  text-align: center;
}
.Completed { 
  background-color: #d2f8e4;
   color: #2e7d32; 
  }
.actions a {
  display: inline-block;
  text-decoration: none;
  background: #015eac;
  color: #fff;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.9rem;
  transition: background 0.3s ease;
}
.actions a:hover { 
  background: #004d91; 
  }
</style>
</head>
<body>

<header>
  <div class="logo">
    <a href="patient_dashboard.php">
      <img class="nav-img" src="../images/nav-logo.png" alt="">
      <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
    </a>
  </div>
  <nav>
    <a href="patient_dashboard.php">Home</a>
        <a href="book_appointment.php">Book Appointment</a>
        <a href="my_appointments.php">Appointments</a>
        <a href="bed_type.php">Book Bed</a>
        <a href="my_bed_bookings.php">Bed Reservations</a>
        <!-- <a href="my_prescriptions.php">Prescriptions</a> -->
        <a href="my_reports.php">Reports</a>
      <a href="patient_dashboard.php" class="btn-login">Back</a>
  </nav>
</header>

<main>
  <h2 style="text-align:center; margin-bottom:25px; color:#0b2236;">My Prescriptions</h2>

  <!-- Filter Bar -->
  <div class="filter-bar" style="margin-bottom:20px; background:#fff; padding:15px; border-radius:10px; box-shadow:0 6px 20px rgba(6,18,32,0.06);">
    <form method="get" style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
      <input type="text" name="search" placeholder="Search by Doctor or Patient Name" value="<?= htmlspecialchars($search) ?>">
      <input type="date" name="date" value="<?= htmlspecialchars($dateFilter) ?>">
      <select name="sort">
        <option value="desc" <?= $sortOrder == 'desc' ? 'selected' : '' ?>>Newest First</option>
        <option value="asc" <?= $sortOrder == 'asc' ? 'selected' : '' ?>>Oldest First</option>
      </select>
      <button type="submit" style="background:#015eac; color:#fff; border:none; border-radius:6px; padding:8px 16px; cursor:pointer;">Apply</button>
    </form>
  </div>

  <!-- Prescriptions Table -->
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Doctor Name</th>
          <th>Patient Name</th>
          <th>Gender</th>
          <th>Address</th>
          <th>Message</th>
          <th>Status</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($appointments->num_rows > 0): ?>
          <?php while ($row = $appointments->fetch_assoc()): ?>
            <tr>
              <td data-label="Doctor"><?= htmlspecialchars($row['doctor_name']) ?></td>
              <td data-label="Patient"><?= htmlspecialchars($row['patient_name']) ?></td>
              <td data-label="Gender"><?= htmlspecialchars($row['gender']) ?></td>
              <td data-label="Address"><?= htmlspecialchars($row['address']) ?></td>
              <td data-label="Message"><?= htmlspecialchars($row['message']) ?></td>
              <td data-label="Status">
                <span class="status Completed">Completed</span>
              </td>
              <td data-label="Date"><?= htmlspecialchars($row['appointment_date']) ?></td>
              <td class="actions" data-label="Actions">
                <a href="view_prescription.php?id=<?= $row['appointment_id'] ?>" target="_blank">View Prescription</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" style="text-align:center; padding:20px;">No prescriptions found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<footer>
  <p>© 2026 SwasthyaTrack. All Rights Reserved.</p>
</footer>

</body>
</html>
