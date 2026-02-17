<?php
session_start();
include("../config/db.php");

//  Secure Output Function
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Restrict Access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";
$form_data = ['report_name' => ''];

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['report_name'] = trim($_POST['report_name'] ?? '');

    if (empty($form_data['report_name']) || empty($_FILES['report_file']['name'])) {
        $error = "All fields are required.";
    } else {
        $file = $_FILES['report_file'];
        $file_name = basename($file['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_ext !== 'pdf') {
            $error = "Only PDF files are allowed.";
        } elseif ($file['size'] > 10 * 1024 * 1024) {
            $error = "File size exceeds 10MB limit.";
        } else {
            $upload_dir = "../pdf/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $new_name = uniqid("report_", true) . ".pdf";
            $target_path = $upload_dir . $new_name;

            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $stmt = $conn->prepare("INSERT INTO prescriptions (user_id, file_name, file_path) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user_id, $form_data['report_name'], $target_path);

                if ($stmt->execute()) {
                    $success = "Report uploaded successfully!";
                    $form_data = ['report_name' => ''];
                } else {
                    $error = "Database error: " . $conn->error;
                    unlink($target_path);
                }
            } else {
                $error = "File upload failed. Check directory permissions.";
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
<title>Upload Report | SwasthyaTrack</title>
<style>
/* Global Theme (Copied from login.php) */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Roboto','Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
body {
    background: linear-gradient(120deg, #e8f0ff, #f9f9fb);
    color: #000;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
a { text-decoration: none; color: inherit; }

/* Header */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 3%;
    background: #ffffffcc;
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    position: sticky;
    top: 0;
    z-index: 1000;
}
.swasthya-color {
     color: #015eac; 
    }
.track-color {
     color: #f31026;
     }
.logo {
    font-size: 1.5rem;
    font-weight: bold;
}
.nav-img {
    height: 30px;
    width: 40px;
    margin-bottom: 7px;
    vertical-align: middle;
}
nav {
  display: flex;
  gap: 24px;
  align-items: center;
}

nav a {
  font-weight: 500;
  color: #000;
  transition: color 0.3s ease, transform 0.2s ease;
}

nav a:hover {
  color: #f31026;
  transform: translateY(-2px);
}

.btn-login {
  padding: 6px 16px;
  border: 1px solid #015eac;
  border-radius: 8px;
  color: #015eac;
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

.btn-login:hover {
  background-color: #f31026;
  border-color: #fff;
  color: #fff;
}

/* Upload Section */
.upload-section {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-grow: 1;
    padding: 2rem;
}
.upload-card {
    background: #fff;
    padding: 2.5rem;
    border-radius: 16px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    max-width: 450px;
    width: 100%;
    text-align: center;
    animation: fadeInUp 0.8s ease;
}
.upload-card h2 {
    margin-bottom: 1.5rem;
    font-size: 26px;
    color: #015eac;
}
.upload-card form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    text-align: left;
}
.upload-card input[type="text"],
.upload-card input[type="file"] {
    padding: 0.8rem 1rem;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}
.upload-card input:focus {
    border-color: #015eac;
    outline: none;
}
.upload-card button {
    background: #015eac;
    color: #fff;
    border: none;
    padding: 0.9rem;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: 0.3s;
}
.upload-card button:hover {
    background: #1f4fbf;
}

/* Alerts */
.alert-success, .alert-error {
    padding: 0.75rem;
    border-radius: 6px;
    font-size: 0.95rem;
    margin-bottom: 1rem;
}
.alert-success {
     background: #e6ffe6; 
     color: #047a04; 
     border: 1px solid #047a04;
     }
.alert-error {
     background: #ffe6e6; 
     color: #a10000; 
     border: 1px solid #a10000;
     }


/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
}
.modal-content {
    background: #fff;
    padding: 2rem;
    border-radius: 12px;
    border: 1px solid #ccc;
    text-align: center;
    max-width: 320px;
}
.modal-content button {
    background: #015eac;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.modal-content button:hover {
    background: #1f4fbf;
}

/* Footer */
footer {
    background: #015eac;
    color: #fff;
    text-align: center;
    padding: 1rem;
}

/* Animation */
@keyframes fadeInUp {
    from {opacity: 0; transform: translateY(30px);}
    to {opacity: 1; transform: translateY(0);}
}
</style>
</head>
<body>

<header>
    <div class="logo">
        <a href="../Patient/patient_dashboard.php">
            <img class="nav-img" src="../images/nav-logo.png" alt="Logo">
            <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
        </a>
    </div>
    <nav>
        <!-- <a href="patient_dashboard.php">Home</a> -->
        <!-- <a href="book_appointment.php">Book Appointment</a> -->
        <!-- <a href="my_appointments.php">Appointments</a> -->
        <!-- <a href="bed_type.php">Book Bed</a> -->
        <!-- <a href="my_bed_bookings.php">Bed Reservations</a> -->
        <!-- <a href="my_prescriptions.php">Prescriptions</a> -->
        <!-- <a href="my_reports.php">Reports</a> -->
        <a href="my_reports.php" class="btn-login">Back</a>
    </nav>
</header>

<section class="upload-section">
    <div class="upload-card">
        <h2>Upload Your Medical Report</h2>

        <?php if ($success): ?>
            <div class="alert-success"><?= e("✅ " . $success) ?></div>
        <?php elseif ($error): ?>
            <div class="alert-error"><?= e("❌ " . $error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label for="report_name">Report Name</label>
            <input type="text" id="report_name" name="report_name" required value="<?= e($form_data['report_name']) ?>">

            <label for="report_file">Select PDF File</label>
            <input type="file" id="report_file" name="report_file" accept="application/pdf" required>

            <button type="submit">Upload Report</button>
        </form>
    </div>
</section>

<div class="modal" id="successModal">
    <div class="modal-content">
        <h3>Upload Successful!</h3>
        <p>Your report has been uploaded successfully. You can view it under “My Reports”.</p>
        <button id="closeModal">OK</button>
    </div>
</div>

<footer>
    <p>&copy; 2026 SwasthyaTrack. All Rights Reserved.</p>
</footer>

<script>
document.addEventListener("DOMContentLoaded", () => {
    <?php if ($success): ?>
        document.getElementById('successModal').style.display = 'flex';
    <?php endif; ?>

    document.getElementById('closeModal').addEventListener('click', () => {
        window.location.href = './my_reports.php';
    });
});
</script>

</body>
</html>
