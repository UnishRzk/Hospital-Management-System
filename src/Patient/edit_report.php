<?php
session_start();
include("../config/db.php");

// Restrict access to logged-in patients
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ======================================
// VALIDATE REPORT ID
// ======================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my_reports.php");
    exit();
}

$report_id = (int)$_GET['id'];

// ======================================
// FETCH EXISTING REPORT DETAILS
// ======================================
$stmt = $conn->prepare("SELECT * FROM prescriptions WHERE prescription_id = ? AND user_id = ?");
$stmt->bind_param("ii", $report_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my_reports.php?error=not_found");
    exit();
}

$report = $result->fetch_assoc();

// ======================================
// HANDLE FORM SUBMISSION
// ======================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['file_name']);

    if ($new_name === '') {
        $error = "Report name is required.";
    } else {
        $upload_dir = "../pdf/";
        $new_path = $report['file_path']; // default (keep old file)
        $file_updated = false;

        // Check if a new file was uploaded
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['file']['tmp_name'];
            $file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

            if ($file_ext !== 'pdf') {
                $error = "Only PDF files are allowed.";
            } else {
                // Delete old file (if exists)
                if (file_exists($report['file_path'])) {
                    unlink($report['file_path']);
                }

                // Generate new unique file path
                $new_filename = "report_" . uniqid() . ".pdf";
                $new_path = $upload_dir . $new_filename;

                if (move_uploaded_file($file_tmp, $new_path)) {
                    $file_updated = true;
                } else {
                    $error = "Failed to upload new file.";
                }
            }
        }

        // If no error, update database
        if (!isset($error)) {
            $update_stmt = $conn->prepare("UPDATE prescriptions SET file_name = ?, file_path = ? WHERE prescription_id = ? AND user_id = ?");
            $update_stmt->bind_param("ssii", $new_name, $new_path, $report_id, $user_id);

            if ($update_stmt->execute()) {
                header("Location: my_reports.php?update=success");
                exit();
            } else {
                $error = "Failed to update report.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Report — SwasthyaTrack</title>
<link rel="stylesheet" href="../css/patient-dashboard.css">
<style>
main {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 3%;
    background: linear-gradient(180deg, #f1f7ff, #eef6ff);
    min-height: 100vh;
}

.edit-container {
    background: #fff;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 8px 24px rgba(6, 18, 32, 0.08);
    width: 100%;
    max-width: 500px;
}

.edit-container h2 {
    text-align: center;
    color: #0b2236;
    margin-bottom: 25px;
}

form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #0b2236;
}

form input[type="text"],
form input[type="file"] {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 10px;
    margin-bottom: 15px;
    font-size: 1rem;
}

form button {
    width: 100%;
    background-color: #015eac;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 10px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button:hover {
    background-color: #004d91;
}

.error {
    color: red;
    text-align: center;
    font-weight: 600;
    margin-bottom: 15px;
}

.back-link {
    display: inline-block;
    margin-top: 15px;
    text-align: center;
    width: 100%;
    color: #015eac;
    text-decoration: none;
    font-weight: 500;
}

.back-link:hover {
    text-decoration: underline;
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
        <a href="../auth/logout.php" class="btn-login">Logout</a>
    </nav>
</header>

<main>
    <div class="edit-container">
        <h2>Edit Report</h2>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label for="file_name">Report Name</label>
            <input type="text" name="file_name" id="file_name" value="<?= htmlspecialchars($report['file_name']) ?>" required>

            <label for="file">Replace PDF (optional)</label>
            <input type="file" name="file" id="file" accept=".pdf">

            <button type="submit">Save Changes</button>
        </form>

        <a href="my_reports.php" class="back-link">← Back to My Reports</a>
    </div>
</main>

<footer>
    <p>© 2026 SwasthyaTrack. All Rights Reserved.</p>
</footer>

</body>
</html>
