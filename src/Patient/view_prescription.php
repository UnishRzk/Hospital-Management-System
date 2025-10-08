<?php
// view_prescription.php
session_start();
include("../config/db.php");

// Restrict to logged-in patient
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($appointment_id <= 0) {
    die("Invalid appointment id.");
}

// Fetch appointment + doctor info (Removed LEFT JOIN patients to ensure patient data comes from the appointments table)
$sql = "SELECT a.*, d.name AS doctor_name, d.phone AS doctor_phone, d.photo AS doctor_photo
        FROM appointments a
        LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
        WHERE a.appointment_id = ? AND a.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $appointment_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("No prescription found for this appointment (or you don't have permission).");
}

$row = $result->fetch_assoc();

// Extract fields - Patient details now use the columns directly from the 'appointments' table (a.*)
$patient_name     = $row['patient_name'] ?? 'Unknown';
$patient_gender   = $row['gender'] ?? '';
$patient_phone    = $row['phone'] ?? '';
$patient_email    = $row['email'] ?? '';
$patient_address  = $row['address'] ?? '';
$appointment_date = $row['appointment_date'] ?? '';
$message          = $row['message'] ?? '';
$doctor_comments  = $row['comment'] ?? '';
$doctor_name      = "Dr. " . ($row['doctor_name'] ?? 'Doctor');

function safe_html($s) {
    return nl2br(htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription - Appointment #<?php echo $appointment_id; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f9fafb; color: #333; }
        .container { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 900px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 12px; margin-bottom: 20px; }
        .logo { display: flex; align-items: center; }
        .logo img { height: 60px; margin-right: 12px; }
        .logo h1 { margin: 0; font-size: 26px; }
        .print-btn { background: #015eac; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .print-btn:hover { background: #004a89; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .info-box { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb; }
        .info-box strong { display: inline-block; width: 120px; color: #015eac; }
        .section { margin-bottom: 20px; }
        .section h2 { font-size: 18px; margin-bottom: 8px; color: #015eac; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        .box { border: 1px solid #ddd; padding: 15px; min-height: 120px; border-radius: 8px; background: #fafafa; }
        .signature { text-align: right; margin-top: 40px; font-size: 14px; font-style: italic; }
        .footer { margin-top: 40px; font-size: 12px; color: #666; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
        @media print {
            .print-btn { display: none; }
            body { background: #fff; margin: 0; }
            .container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="../images/nav-logo.png" alt="Logo">
                <h1><span style="color:#015eac;">Swasthya</span><span style="color:#f31026;">Track</span></h1>
            </div>
            <button class="print-btn" onclick="window.print()">🖨 Print</button>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <p><strong>Patient Name:</strong> <?php echo htmlspecialchars($patient_name); ?></p>
                <p><strong>Gender:</strong> <?php echo htmlspecialchars(ucfirst($patient_gender)); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($patient_phone); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($patient_email); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($patient_address); ?></p>
            </div>
            <div class="info-box">
                <p><strong>Doctor:</strong> <?php echo htmlspecialchars($doctor_name); ?></p>
                <p><strong>Appointment Date:</strong> <?php echo htmlspecialchars($appointment_date); ?></p>
                <p><strong>Patient Note:</strong> <?php echo safe_html($message); ?></p>
            </div>
        </div>

        <div class="section">
            <h2>Prescription</h2>
            <div class="box">
                <?php echo $doctor_comments ? safe_html($doctor_comments) : '<em>No written prescription.</em>'; ?>
            </div>
        </div>

        <div class="signature">
            Prescribed By:<br>
            <strong><?php echo htmlspecialchars($doctor_name); ?></strong>
        </div>

        <div class="footer">
            © SwasthyaTrack · Prescription · Printed on: <?php echo date('Y-m-d'); ?>
        </div>
    </div>
</body>
</html>