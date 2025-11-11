<?php
session_start();
include("../config/db.php");

// Restrict to logged-in patients
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ==========================
// HANDLE DELETE ACTION
// ==========================
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    // Use prepared statements for secure operations
    
    // 1. Fetch file path before deletion
    $stmt = $conn->prepare("SELECT file_path FROM prescriptions WHERE prescription_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $file_path = $row['file_path'];

        // 2. Delete DB record
        $delete_stmt = $conn->prepare("DELETE FROM prescriptions WHERE prescription_id = ? AND user_id = ?");
        $delete_stmt->bind_param("ii", $delete_id, $user_id);
        
        if ($delete_stmt->execute()) {
            // 3. Delete physical file (basic security check)
            // Ensure the file path is within expected directory to prevent Path Traversal
            // For a production system, you'd add stringent path validation here.
            if (file_exists($file_path) && strpos(realpath($file_path), realpath('../uploads/reports')) === 0) {
                        unlink($file_path);
            }
            header("Location: my_reports.php?delete=success");
            exit();
        } else {
            header("Location: my_reports.php?delete=failed");
            exit();
        }
    } else {
        header("Location: my_reports.php?delete=not_found");
        exit();
    }
}

// ============================
// FILTERS & SEARCH
// ============================
$search = $_GET['search'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$sortOrder = $_GET['sort'] ?? 'desc'; // 'desc' for Newest First

// ============================
// FETCH REPORTS
// ============================
$sql = "SELECT prescription_id, file_name, file_path, uploaded_at 
        FROM prescriptions 
        WHERE user_id = ?";

$params = [$user_id];
$types = "i";

if ($search !== '') {
    $sql .= " AND file_name LIKE ?";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $types .= "s";
}

if ($dateFilter !== '') {
    $sql .= " AND DATE(uploaded_at) = ?";
    $params[] = $dateFilter;
    $types .= "s";
}

$orderSQL = ($sortOrder === 'asc') ? "ASC" : "DESC";
$sql .= " ORDER BY uploaded_at $orderSQL";

$stmt = $conn->prepare($sql);

// Dynamically bind parameters using call_user_func_array
if ($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$reports = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Reports — SwasthyaTrack</title>
<link rel="stylesheet" href="../css/patient-dashboard.css">
<style>
/* Page Container */
main {
    padding: 40px 3%;
    padding-top: 20px;
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
    align-items: center; /* Vertically align items */
}

.filter-bar input,
.filter-bar select {
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 10px;
    font-size: 0.95rem;
    min-width: 180px;
}


/* MODIFIED: Unified button styles for Apply and Upload */
.filter-bar button, .filter-bar a.btn {
    color: #fff;
    background-color: #015eac; /* Primary blue color */
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.filter-bar button:hover, .filter-bar a.btn:hover {
    background-color: #004d91; /* Darker blue on hover */
}

.btn{
    height: 38px;
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

/* Actions Styling */
.action-icons {
    display: flex;
    gap: 15px;
}

.action-icons a {
    text-decoration: none;
    font-size: 1.1rem;
    transition: transform 0.2s ease;
}

.action-icons a:hover {
    transform: scale(1.2);
}

.action-icons a.edit { color: #015eac; }
.action-icons a.delete { color: #f31026; }

/* MODIFIED: View Report Button Styling to match Apply button */
.view-report-btn {
    display: inline-block;
    padding: 10px 20px;
    background-color: #015eac;
    color: #fff;
    border: none;
    border-radius: 10px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: background-color 0.3s ease;
    text-align: center;
    cursor: pointer;
}

.view-report-btn:hover {
    background-color: #004d91;
}


/* Responsive Table */
@media(max-width: 768px) {
    table, thead, tbody, th, td, tr { display: block; width: 100%; }
    thead { display: none; }
    tr { margin-bottom: 15px; background: #fff; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    td {
        display: flex; 
        justify-content: space-between;
        padding: 12px 16px; 
        border: none;
        align-items: center;
    }
    td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #015eac;
        flex-shrink: 0;
    }
    
    .action-icons {
        /* Inherits styles from above */
    }

    td[data-label="View"] .view-report-btn {
        margin-left: auto;
    }
}
</style>

<script>
function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this report? This action cannot be undone.")) {
        window.location.href = "my_reports.php?delete_id=" + id;
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
        <!-- <a href="book_appointment.php">Book Appointment</a> -->
        <a href="my_appointments.php">Appointments</a>
        <!-- <a href="bed_type.php">Book Bed</a> -->
        <a href="my_bed_bookings.php">Bed Reservations</a>
        <a href="my_prescriptions.php">Prescriptions</a>
        <!-- <a href="my_reports.php">Reports</a> -->
      <a href="patient_dashboard.php" class="btn-login">Back</a>
    </nav>
</header>

<main>
    <h2 style="text-align:center; margin-bottom:25px; color:#0b2236;">My Reports</h2>

    <?php if (isset($_GET['delete']) && $_GET['delete'] === 'success'): ?>
        <p style="text-align:center; color:green; font-weight:600;">Report deleted successfully. ✅</p>
    <?php elseif (isset($_GET['delete']) && $_GET['delete'] === 'failed'): ?>
        <p style="text-align:center; color:red; font-weight:600;">Failed to delete report. Please try again. ❌</p>
    <?php elseif (isset($_GET['delete']) && $_GET['delete'] === 'not_found'): ?>
        <p style="text-align:center; color:red; font-weight:600;">Report not found or you don't have permission. ⚠️</p>
    <?php endif; ?>

    <div class="filter-bar">
        <form method="get">
            <input type="text" name="search" placeholder="Search by File Name" value="<?= htmlspecialchars($search) ?>">
            <input type="date" name="date" value="<?= htmlspecialchars($dateFilter) ?>">
            <select name="sort">
                <option value="desc" <?= $sortOrder == 'desc' ? 'selected' : '' ?>>Newest First</option>
                <option value="asc" <?= $sortOrder == 'asc' ? 'selected' : '' ?>>Oldest First</option>
            </select>
            <button type="submit">Apply</button>
            <a href="upload_report.php" class="btn">+ Upload Report</a>
        </form>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>S.N.</th>
                    <th>File Name</th>
                    <th>Uploaded Date</th>
                    <th>Actions</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $sn = 1; ?>
                <?php if ($reports->num_rows > 0): ?>
                    <?php while ($row = $reports->fetch_assoc()): ?>
                        <tr>
                            <td data-label="S.N."><?= $sn++ ?></td>
                            <td data-label="File Name"><?= htmlspecialchars($row['file_name']) ?></td>
                            <td data-label="Uploaded Date"><?= date('Y-m-d H:i', strtotime($row['uploaded_at'])) ?></td>
                            
                            <td data-label="Actions">
                                <div class="action-icons">
                                    <a href="edit_report.php?id=<?= $row['prescription_id'] ?>" class="edit" title="Edit Report Details">✏️</a>
                                    <a href="javascript:void(0);" onclick="confirmDelete(<?= $row['prescription_id'] ?>)" class="delete" title="Delete Report">🗑️</a>
                                </div>
                            </td>
                            <td data-label="View">
                                <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="view-report-btn">View Report</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:20px;">No reports found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<footer>
    <p>© 2025 SwasthyaTrack. All Rights Reserved.</p>
</footer>

</body>
</html>