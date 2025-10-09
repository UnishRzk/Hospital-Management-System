<?php
session_start();
include("../config/db.php");

// ============================
// ACCESS CONTROL
// ============================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ============================
// CANCEL BED LOGIC
// ============================
if (isset($_GET['cancel_bed_id']) && is_numeric($_GET['cancel_bed_id'])) {
    $cancel_bed_id = (int) $_GET['cancel_bed_id'];

    // Verify bed belongs to this patient
    $stmt = $conn->prepare("SELECT bed_id FROM beds WHERE bed_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cancel_bed_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Apply same logic as edit_bed.php when status = Empty
        $update = $conn->prepare("
            UPDATE beds 
            SET 
                patient_name = NULL,
                gender = NULL,
                contact = NULL,
                email = NULL,
                address = NULL,
                reason_for_admission = NULL,
                reserved_date = NULL,
                status = 'Empty',
                user_id = NULL
            WHERE bed_id = ?
        ");
        $update->bind_param("i", $cancel_bed_id);

        if ($update->execute()) {
            header("Location: my_bed_bookings.php?success=" . urlencode("Bed booking cancelled successfully."));
            exit();
        } else {
            header("Location: my_bed_bookings.php?error=" . urlencode("Failed to cancel booking. Please try again."));
            exit();
        }
    }
}

// ============================
// FILTERS
// ============================
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$sortOrder = $_GET['sort'] ?? 'desc';

// ============================
// FETCH BED BOOKINGS
// ============================
$sql = "SELECT bed_id, patient_name, gender, address, reason_for_admission,
               reserved_date, type, status, created_at
        FROM beds
        WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if ($search !== '') {
    $sql .= " AND (patient_name LIKE ? OR address LIKE ? OR reason_for_admission LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    $types .= "sss";
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

if ($dateFilter !== '') {
    $sql .= " AND DATE(reserved_date) = ?";
    $params[] = $dateFilter;
    $types .= "s";
}

$orderSQL = ($sortOrder === 'asc') ? "ASC" : "DESC";
$sql .= " ORDER BY reserved_date $orderSQL";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$beds = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Bed Bookings — SwasthyaTrack</title>
<link rel="stylesheet" href="../css/patient-dashboard.css">
<style>
main {
    padding: 40px 3%;
    background: linear-gradient(180deg, #f1f7ff, #eef6ff);
    min-height: 100vh;
}

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

.status {
    padding: 6px 10px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    text-align: center;
}
.Empty { background-color: #f0f0f0; color: #666; }
.Reserved { background-color: #f5b91433; color: #b57d00; }
.Occupied { background-color: #d2f8e4; color: #2e7d32; }
.Out\ of\ Order { background-color: #ffdddd; color: #d63031; }

.actions a {
    margin-right: 10px;
    text-decoration: none;
    font-size: 1.1rem;
    transition: transform 0.2s ease;
}
.actions a:hover { transform: scale(1.2); }
.actions a.edit { color: #015eac; }
.actions a.delete { color: #f31026; }

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
function confirmCancel(id) {
    if (confirm("Are you sure you want to cancel this bed booking?")) {
        window.location.href = "my_bed_bookings.php?cancel_bed_id=" + id;
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
        <a href="../auth/logout.php" class="btn-login">Logout</a>
    </nav>
</header>

<main>
    <?php if (isset($_GET['success'])): ?>
        <div style="background:#d4edda;color:#155724;padding:10px 15px;border-radius:8px;margin-bottom:15px;text-align:center;">
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div style="background:#f8d7da;color:#721c24;padding:10px 15px;border-radius:8px;margin-bottom:15px;text-align:center;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <h2 style="text-align:center; margin-bottom:25px; color:#0b2236;">My Bed Bookings</h2>

    <div class="filter-bar">
        <form method="get">
            <input type="text" name="search" placeholder="Search by Name or Reason" value="<?= htmlspecialchars($search) ?>">
            <select name="status">
                <option value="">Status</option>
                <option value="Reserved" <?= $statusFilter == 'Reserved' ? 'selected' : '' ?>>Reserved</option>
                <option value="Occupied" <?= $statusFilter == 'Occupied' ? 'selected' : '' ?>>Occupied</option>
            </select>
            <select name="type">
                <option value="">Type</option>
                <option value="General" <?= $typeFilter == 'General' ? 'selected' : '' ?>>General</option>
                <option value="Semi-Private" <?= $typeFilter == 'Semi-Private' ? 'selected' : '' ?>>Semi-Private</option>
                <option value="Private" <?= $typeFilter == 'Private' ? 'selected' : '' ?>>Private</option>
            </select>
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
                    <th>Bed ID</th>
                    <th>Patient Name</th>
                    <th>Gender</th>
                    <th>Address</th>
                    <th>Reason</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Reserved Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($beds->num_rows > 0): ?>
                    <?php while ($row = $beds->fetch_assoc()): ?>
                        <tr>
                            <td data-label="Bed ID"><?= htmlspecialchars($row['bed_id']) ?></td>
                            <td data-label="Patient"><?= htmlspecialchars($row['patient_name']) ?></td>
                            <td data-label="Gender"><?= htmlspecialchars($row['gender']) ?></td>
                            <td data-label="Address"><?= htmlspecialchars($row['address']) ?></td>
                            <td data-label="Reason"><?= htmlspecialchars($row['reason_for_admission']) ?></td>
                            <td data-label="Type"><?= htmlspecialchars($row['type']) ?></td>
                            <td data-label="Status">
                                <span class="status <?= htmlspecialchars($row['status']) ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                            <td data-label="Reserved Date"><?= htmlspecialchars($row['reserved_date']) ?></td>
                            <td class="actions" data-label="Actions">
                                <a href="edit_bed.php?id=<?= $row['bed_id'] ?>" class="edit">✏️</a>
                                <?php if ($row['status'] === 'Reserved'): ?>
                                    <a href="javascript:void(0);" onclick="confirmCancel(<?= $row['bed_id'] ?>)" class="delete">🗑️</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" style="text-align:center; padding:20px;">No bed bookings found.</td></tr>
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
