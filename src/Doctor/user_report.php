<?php
session_start();
include("../config/db.php");

// Restrict to logged-in doctors
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

// Validate and fetch target patient user_id
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    header("Location: doctor_dashboard.php");
    exit();
}
$patient_user_id = (int)$_GET['user_id'];

// ============================
// FILTERS & SEARCH
// ============================
$search = $_GET['search'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$sortOrder = $_GET['sort'] ?? 'desc';

// ============================
// FETCH REPORTS FOR PATIENT
// ============================
$sql = "SELECT prescription_id, file_name, file_path, uploaded_at 
        FROM prescriptions 
        WHERE user_id = ?";

$params = [$patient_user_id];
$types = "i";

if ($search !== '') {
    $sql .= " AND file_name LIKE ?";
    $params[] = "%$search%";
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
$stmt->bind_param($types, ...$params);
$stmt->execute();
$reports = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Reports | Doctor Panel</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f1f7ff;
    margin: 0;
    padding: 0;
}
header {
    background-color: #015eac;
    color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
header h1 { font-size: 1.4rem; margin: 0; }
header a {
    color: white;
    text-decoration: none;
    background: #004d91;
    padding: 8px 14px;
    border-radius: 6px;
}
main {
    padding: 30px;
    max-width: 900px;
    margin: auto;
}
.filter-bar {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
}
.filter-bar input, .filter-bar select, .filter-bar button {
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
}
.filter-bar button {
    background-color: #015eac;
    color: white;
    border: none;
    cursor: pointer;
}
.filter-bar button:hover { background-color: #004d91; }

.table-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}
table {
    width: 100%;
    border-collapse: collapse;
}
thead {
    background-color: #015eac;
    color: white;
}
th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}
tbody tr:hover {
    background: #f9fbff;
}
.view-btn {
    background-color: #015eac;
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9rem;
}
.view-btn:hover {
    background-color: #004d91;
}
footer {
    text-align: center;
    padding: 15px;
    color: #666;
    font-size: 0.9rem;
    margin-top: 40px;
}
</style>
</head>

<body>
<header>
    <h1>Patient Reports</h1>
    <a href="doctor_dashboard.php">Back</a>
</header>

<main>
    <div class="filter-bar">
        <form method="get">
            <input type="hidden" name="user_id" value="<?= $patient_user_id ?>">
            <input type="text" name="search" placeholder="Search by File Name" value="<?= htmlspecialchars($search) ?>">
            <input type="date" name="date" value="<?= htmlspecialchars($dateFilter) ?>">
            <select name="sort">
                <option value="desc" <?= $sortOrder == 'desc' ? 'selected' : '' ?>>Newest First</option>
                <option value="asc" <?= $sortOrder == 'asc' ? 'selected' : '' ?>>Oldest First</option>
            </select>
            <button type="submit">Apply</button>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>S.N.</th>
                    <th>File Name</th>
                    <th>Uploaded Date</th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                <?php $sn = 1; ?>
                <?php if ($reports->num_rows > 0): ?>
                    <?php while ($row = $reports->fetch_assoc()): ?>
                        <tr>
                            <td><?= $sn++ ?></td>
                            <td><?= htmlspecialchars($row['file_name']) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($row['uploaded_at'])) ?></td>
                            <td><a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="view-btn">View Report</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; padding:20px;">No reports found for this patient.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<footer>
    © 2026 SwasthyaTrack. All Rights Reserved.
</footer>
</body>
</html>
