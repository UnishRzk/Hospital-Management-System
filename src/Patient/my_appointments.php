<?php
session_start();
include("../config/db.php");

// Restrict to logged-in patients only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

// Get patient_id from session user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Patient record not found.");
}
$patient_id = $result->fetch_assoc()['patient_id'];

// ---------------- FILTER ----------------
$search = $_GET['search'] ?? '';

// ---------------- QUERY ----------------
$sql = "SELECT a.appointment_id, a.patient_name, a.gender, a.address, a.message, 
               a.status, a.appointment_date, d.name AS doctor_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.doctor_id
        WHERE a.patient_id = ? 
        AND (a.status = 'Booked' OR a.status = 'Cancelled')";

$params = [$patient_id];
$types = "i";

if ($search !== '') {
    $sql .= " AND (a.patient_name LIKE ? OR d.name LIKE ? OR a.address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "sss";
}

$sql .= " ORDER BY a.appointment_date DESC";

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
    .header img {
        width: 40px;
    }
    .header h1 {
        color: #015eac; font-size: 1.4rem;
    }
    .logout {
        background: #015eac; color: #fff; text-decoration: none;
        padding: 8px 15px; border-radius: 4px; transition: 0.3s;
    }
    .logout:hover { background: #004d91; }

    /* Filter Bar */
    .filter-bar {
        display: flex; justify-content: center; margin-bottom: 20px;
    }
    .filter-bar form {
        display: flex; gap: 10px; flex-wrap: wrap;
    }
    .filter-bar input {
        padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; width: 250px;
    }
    .filter-bar button {
        background: #015eac; color: #fff; border: none; padding: 8px 15px;
        border-radius: 4px; cursor: pointer; transition: 0.3s;
    }
    .filter-bar button:hover { background: #004d91; }

    /* Table */
    .table-container {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    table {
        width: 100%; border-collapse: collapse;
    }
    th, td {
        padding: 12px 15px; border-bottom: 1px solid #eee;
        text-align: left; font-size: 0.95rem;
    }
    thead th {
        background: #f0f0f0;
        font-weight: bold;
        position: sticky; top: 0;
    }

    /* Status */
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
</head>
<body>

<div class="header">
    <div class="logo">
        <img src="../images/nav-logo.png" alt="logo">
        <h1>My Appointments</h1>
    </div>
    <a href="../auth/logout.php" class="logout">Logout</a>
</div>

<div class="filter-bar">
    <form method="get">
        <input type="text" name="search" placeholder="Search by Doctor, Address, or Name" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>
</div>
<?php echo "<pre>Patient ID: $patient_id</pre>";?>

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
                <th>Appointment Date</th>
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
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;">No appointments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
