<?php
session_start();
include("../config/db.php");

// Restrict to logged-in patients
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ============================
// HANDLE CANCEL ACTION
// ============================
if (isset($_GET['cancel_id']) && is_numeric($_GET['cancel_id'])) {
    $cancel_id = (int)$_GET['cancel_id'];

    // Check if appointment belongs to this patient and is Booked
    $stmt = $conn->prepare("SELECT status FROM appointments WHERE appointment_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cancel_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $appt = $res->fetch_assoc();
        if ($appt['status'] === 'Booked') {
            $update = $conn->prepare("UPDATE appointments SET status = 'Cancelled', updated_at = NOW() WHERE appointment_id = ? AND user_id = ?");
            $update->bind_param("ii", $cancel_id, $user_id);
            if ($update->execute()) {
                header("Location: my_appointments.php?cancel=success");
                exit();
            } else {
                header("Location: my_appointments.php?cancel=failed");
                exit();
            }
        } else {
            header("Location: my_appointments.php?cancel=not_allowed");
            exit();
        }
    } else {
        header("Location: my_appointments.php?cancel=not_found");
        exit();
    }
}

// ============================
// FILTERS
// ============================
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$sortOrder = $_GET['sort'] ?? 'desc';

// ============================
// FETCH APPOINTMENTS
// ============================
$sql = "SELECT a.appointment_id, a.patient_name, a.gender, a.address, a.message,
               a.status, a.appointment_date, d.name AS doctor_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.doctor_id
        WHERE a.user_id = ?
          AND (a.status IN ('Booked', 'Cancelled', 'Completed'))";

$params = [$user_id];
$types = "i";

if ($search !== '') {
    $sql .= " AND (a.patient_name LIKE ? OR d.name LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
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
<title>My Appointments — SwasthyaTrack</title>
<link rel="stylesheet" href="../css/patient-dashboard.css">
<style>
/* Page Container */
main {
  padding: 40px 3%;
  padding-top: 30px;
  background: linear-gradient(180deg, #f1f7ff, #eef6ff);
  min-height: 100vh;
}

/* Filter Bar */
.filter-bar {
  background-color: #fff;
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(6, 18, 32, 0.06);
  padding: 20px;
  margin-bottom: 25px;
}

.filter-bar form {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
}

.filter-bar input,
.filter-bar select {
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 10px;
  font-size: 0.95rem;
  min-width: 180px;
}

.filter-bar button {
  background-color: #015eac;
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 10px 20px;
  cursor: pointer;
  transition: background 0.3s ease;
  font-weight: 500;
}

.filter-bar button:hover {
  background-color: #004d91;
}

/* Table */
.table-container {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(6, 18, 32, 0.08);
  overflow: hidden;
}

table {
  width: 100%;
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

/* Status Styles */
.status {
  padding: 6px 10px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.85rem;
  text-align: center;
}

.Booked { background-color: #f5b91433; color: #b57d00; }
.Cancelled { background-color: #ffdddd; color: #d63031; }
.Completed { background-color: #d2f8e4; color: #2e7d32; }

/* Actions */
.actions a {
  margin-right: 10px;
  text-decoration: none;
  font-size: 1.1rem;
  transition: transform 0.2s ease;
}

.actions a:hover {
  transform: scale(1.2);
}

.actions a.edit { color: #015eac; }
.actions a.delete { color: #f31026; }

/* Responsive Table */
@media(max-width: 768px) {
  table, thead, tbody, th, td, tr { display: block; width: 100%; }
  thead { display: none; }
  tr { margin-bottom: 15px; background: #fff; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
  td {
    display: flex; justify-content: space-between;
    padding: 12px 16px; border: none;
  }
  td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #015eac;
  }
}
</style>

<script>
function confirmDelete(id) {
  if (confirm("Are you sure you want to cancel this appointment?")) {
    window.location.href = "my_appointments.php?cancel_id=" + id;
  }
}
</script>
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
        <!-- <a href="my_appointments.php">Appointments</a> -->
        <!-- <a href="bed_type.php">Book Bed</a> -->
        <a href="my_bed_bookings.php">Bed Reservations</a>
        <a href="my_prescriptions.php">Prescriptions</a>
        <a href="my_reports.php">Reports</a>
      <a href="patient_dashboard.php" class="btn-login">Back</a>
  </nav>
</header>

<main>
  <h2 style="text-align:center; margin-bottom:25px; color:#0b2236;">My Appointments</h2>

  <?php if (isset($_GET['cancel']) && $_GET['cancel'] === 'success'): ?>
    <p style="text-align:center; color:green; font-weight:600;">Appointment cancelled successfully.</p>
  <?php elseif (isset($_GET['cancel']) && $_GET['cancel'] === 'not_allowed'): ?>
    <p style="text-align:center; color:red; font-weight:600;">You can only cancel booked appointments.</p>
  <?php elseif (isset($_GET['cancel']) && $_GET['cancel'] === 'failed'): ?>
    <p style="text-align:center; color:red; font-weight:600;">Failed to cancel appointment. Please try again.</p>
  <?php endif; ?>

  <!-- Filter Bar -->
  <div class="filter-bar">
    <form method="get">
      <input type="text" name="search" placeholder="Search by Doctor or Patient Name" value="<?= htmlspecialchars($search) ?>">
      <select name="status">
        <option value="">Status</option>
        <option value="Booked" <?= $statusFilter == 'Booked' ? 'selected' : '' ?>>Booked</option>
        <option value="Completed" <?= $statusFilter == 'Completed' ? 'selected' : '' ?>>Completed</option>
        <option value="Cancelled" <?= $statusFilter == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
      </select>
      <input type="date" name="date" value="<?= htmlspecialchars($dateFilter) ?>">
      <select name="sort">
        <option value="desc" <?= $sortOrder == 'desc' ? 'selected' : '' ?>>Newest First</option>
        <option value="asc" <?= $sortOrder == 'asc' ? 'selected' : '' ?>>Oldest First</option>
      </select>
      <button type="submit">Apply</button>
    </form>
  </div>

  <!-- Appointments Table -->
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
                <span class="status <?= htmlspecialchars($row['status']) ?>">
                  <?= htmlspecialchars($row['status']) ?>
                </span>
              </td>
              <td data-label="Date"><?= htmlspecialchars($row['appointment_date']) ?></td>
              <td class="actions" data-label="Actions">
                <a href="edit_appointment.php?id=<?= $row['appointment_id'] ?>" class="edit">✏️</a>
                <?php if ($row['status'] === 'Booked'): ?>
                  <a href="javascript:void(0);" onclick="confirmDelete(<?= $row['appointment_id'] ?>)" class="delete">🗑️</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" style="text-align:center; padding:20px;">No appointments found.</td></tr>
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
