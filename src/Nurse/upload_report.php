<?php
session_start();
include("../config/db.php");

// Secure Output Function 
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Restrict Access: nurses only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'nurse') {
    header("Location: ../auth/login.php");
    exit();
}

// Validate user_id from GET
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($user_id <= 0) {
    http_response_code(400);
    echo "Invalid user ID.";
    exit();
}

// Verify patient exists
$stmt = $conn->prepare("SELECT p.name, p.email FROM patients p WHERE p.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$patientResult = $stmt->get_result();
if ($patientResult->num_rows === 0) {
    http_response_code(404);
    echo "Patient not found.";
    exit();
}
$patient = $patientResult->fetch_assoc();

// Initialize
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
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = "File upload error (code " . (int)$file['error'] . ").";
        } else {
            $upload_dir = "../pdf/";
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    $error = "Unable to create upload directory.";
                }
            }

            if (empty($error)) {
                $new_name = uniqid("report_", true) . ".pdf";
                $target_path = $upload_dir . $new_name;

                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    @chmod($target_path, 0644);
                    $insert = $conn->prepare("INSERT INTO prescriptions (user_id, file_name, file_path) VALUES (?, ?, ?)");
                    $insert->bind_param("iss", $user_id, $form_data['report_name'], $target_path);

                    if ($insert->execute()) {
                        $success = "Report uploaded successfully!";
                        $form_data = ['report_name' => ''];
                    } else {
                        $error = "Database error: " . $conn->error;
                        if (file_exists($target_path)) @unlink($target_path);
                    }
                    $insert->close();
                } else {
                    $error = "File upload failed. Check directory permissions.";
                }
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
<title>Upload Report | Nurse Panel</title>
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
    padding: 40px; 
    overflow-y: auto; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
  }

  /* Upload Card */
  .upload-card {
    background: #fff;
    padding: 2.5rem;
    border-radius: 16px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    max-width: 600px;
    width: 100%;
    text-align: center;
    animation: fadeInUp 0.6s ease;
  }
  .upload-card h2 { 
    margin-bottom: 0.5rem; 
    font-size: 22px; 
    color: #015eac; 
  }
  .upload-card p.patient-info {
     margin-bottom: 1rem; 
     color: #333; font-size: 0.95rem; 
    }

  /* Form */
  .upload-card form { 
    display: flex; 
    flex-direction: column; 
    gap: 1rem; 
    text-align: left; 
  }

  .upload-card label { 
    font-size: 0.95rem; 
    color: #333; 
  }

  .upload-card input[type="text"], .upload-card input[type="file"] {
    padding: 0.8rem 1rem;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
    width: 100%;
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

  .btn-cancel {
    background: #ccc;
    color: #000;
  }
  
  .btn-cancel:hover { 
    background: #bbb; 
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
    top: 0; 
    left: 0;
    width: 100%; 
    height: 100%;
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
    max-width: 420px;
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

  /* Animation */
  @keyframes fadeInUp { from {
    opacity: 0; 
    transform: translateY(30px);
  } to 
  {
    opacity: 1; 
    transform: translateY(0);
  } }

  @media (max-width: 600px) {
    .sidebar { 
      width: 200px; 
    }
    .upload-card { 
      padding: 1.2rem; 
    }
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

<!-- Main Content -->
<div class="main">
  <div class="upload-card">
    <h2>Upload Medical Report for Patient</h2>
    <p class="patient-info">
      Patient: <strong><?= e($patient['name']) ?></strong>
      <?php if (!empty($patient['email'])): ?>
        &nbsp;|&nbsp; Email: <?= e($patient['email']) ?>
      <?php endif; ?>
    </p>

    <?php if ($success): ?>
      <div class="alert-success"><?= e("✅ " . $success) ?></div>
    <?php elseif ($error): ?>
      <div class="alert-error"><?= e("❌ " . $error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" novalidate>
      <label for="report_name">Report Name</label>
      <input type="text" id="report_name" name="report_name" required value="<?= e($form_data['report_name']) ?>">

      <label for="report_file">Select PDF File</label>
      <input type="file" id="report_file" name="report_file" accept="application/pdf" required>

      <div style="display:flex; gap:.5rem; justify-content:flex-end;">
        <button type="button" class="btn-cancel" onclick="window.location.href='manage_reports.php'">Cancel</button>
        <button type="submit">Upload Report</button>
      </div>
    </form>
  </div>
</div>

<!-- Success Modal -->
<div class="modal" id="successModal">
  <div class="modal-content">
    <h3>Upload Successful!</h3>
    <p>The report has been uploaded for <?= e($patient['name']) ?>.</p>
    <button id="closeModal">OK</button>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php if ($success): ?>
        document.getElementById('successModal').style.display = 'flex';
    <?php endif; ?>

    var closeBtn = document.getElementById('closeModal');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            window.location.href = 'manage_reports.php';
        });
    }
});
</script>

</body>
</html>
