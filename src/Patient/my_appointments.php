<?php
session_start();
include("../config/db.php");

// Restrict to logged-in patients
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

// Logged-in user_id
$user_id = $_SESSION['user_id'];

// ---------------- FILTERS ----------------
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$sortOrder = $_GET['sort'] ?? 'desc'; // default: newest first

// ---------------- QUERY ----------------
$sql = "SELECT a.appointment_id, a.patient_name, a.gender, a.address, a.message,
               a.status, a.appointment_date, d.name AS doctor_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.doctor_id
        WHERE a.user_id = ?
          AND (a.status = 'Booked' OR a.status = 'Cancelled' OR a.status = 'Completed')";

$params = [$user_id];
$types = "i";

// Search by doctor/patient name
if ($search !== '') {
    $sql .= " AND (a.patient_name LIKE ? OR d.name LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

// Status filter
if ($statusFilter !== '') {
    $sql .= " AND a.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

// Date filter
if ($dateFilter !== '') {
    $sql .= " AND DATE(a.appointment_date) = ?";
    $params[] = $dateFilter;
    $types .= "s";
}

// Sorting
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
<title>My Appointments | SwasthyaTrack</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
    body { background: #f9f9fb; padding: 20px; }

    .header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 20px;
    }
    .header .logo {
        display: flex; align-items: center; gap: 8px;
    }
    .header img { width: 40px; }
    .header h1 { color: #015eac; font-size: 1.4rem; }
    .logout {
        background: #015eac; color: #fff; text-decoration: none;
        padding: 8px 15px; border-radius: 4px; transition: 0.3s;
    }
    .logout:hover { background: #004d91; }

    /* Filter Bar */
    .filter-bar { display: flex; justify-content: center; margin-bottom: 20px; }
    .filter-bar form { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
    .filter-bar input, .filter-bar select, .filter-bar button {
        padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px;
    }
    .filter-bar button {
        background: #015eac; color: #fff; border: none; cursor: pointer; transition: 0.3s;
    }
    .filter-bar button:hover { background: #004d91; }

    /* Table */
    .table-container {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }
    th, td {
        padding: 12px 15px; border-bottom: 1px solid #eee;
        text-align: left; font-size: 0.95rem;
    }
    thead th {
        background: #f0f0f0;
        font-weight: bold;
        position: sticky; top: 0;
    }

    .status {
        padding: 6px 10px;
        border-radius: 4px;
        font-weight: bold;
        display: inline-block;
        text-align: center;
        min-width: 90px;
    }
    .Booked { background-color: #f5b914; color: #000; }
    .Cancelled { background-color: #e74c3c; color: #fff; }
    .Completed { background-color: #2ecc71; color: #fff; }

    .actions a {
        margin-right: 10px;
        text-decoration: none;
        font-size: 1.2rem;
    }
    .actions a.edit { color: #015eac; }
    .actions a.delete { color: #f31026; }

    @media(max-width: 768px) {
        table, thead, tbody, th, td, tr { display: block; }
        th { display: none; }
        td {
            display: flex; justify-content: space-between; border-bottom: 1px solid #ddd;
            padding: 10px;
        }
        td::before {
            content: attr(data-label);
            font-weight: bold;
            color: #015eac;
        }
    }
</style>

<script>
function confirmDelete(id) {
    if (confirm("Are you sure you want to cancel this appointment?")) {
        window.location.href = "cancel_appointment.php?id=" + id;
    }
}
</script>
</head>
<body>

<div class="header">
    <div class="logo">
        <img src="../images/nav-logo.png" alt="logo">
        <h1>My Appointments</h1>
    </div>
    <a href="../auth/logout.php" class="logout">Logout</a>
</div>

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
        <button type="submit">Filter</button>
    </form>
</div>

<!-- Table -->
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
                            <a href="view_appointment.php?id=<?= $row['appointment_id'] ?>" class="edit">✏️</a>
                            <?php if ($row['status'] === 'Booked'): ?>
                                <a href="javascript:void(0);" onclick="confirmDelete(<?= $row['appointment_id'] ?>)" class="delete">🗑️</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align:center;">No appointments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
