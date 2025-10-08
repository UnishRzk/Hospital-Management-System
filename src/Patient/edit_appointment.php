<?php
session_start();
include("../config/db.php");

// ==========================
// ACCESS CONTROL
// ==========================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ==========================
// VALIDATE APPOINTMENT ID
// ==========================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid appointment ID.");
}

$appointment_id = (int)$_GET['id'];

// ==========================
// FETCH APPOINTMENT (Belongs to logged-in user only)
// ==========================
$stmt = $conn->prepare("
    SELECT a.*, d.name AS doctor_name 
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.appointment_id = ? AND a.user_id = ?
");
$stmt->bind_param("ii", $appointment_id, $user_id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    die("Appointment not found or you do not have permission to edit this appointment.");
}

// ==========================
// SAFE OUTPUT FUNCTION
// ==========================
function safe_html($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// ==========================
// HANDLE UPDATE REQUEST
// ==========================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Prevent editing Cancelled or Completed appointments
    if (in_array($appointment['status'], ['Cancelled', 'Completed'])) {
        $error = "You cannot modify a cancelled or completed appointment.";
    } else {
        $patient_name = trim($_POST['patient_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $appointment_date = $_POST['appointment_date'] ?? '';
        $message = trim($_POST['message'] ?? '');
        $status = $_POST['status'] ?? $appointment['status'];

        if (empty($patient_name) || empty($phone) || empty($email) || empty($gender) || empty($appointment_date)) {
            $error = "All required fields must be filled.";
        } else {
            $update = $conn->prepare("
                UPDATE appointments 
                SET patient_name = ?, phone = ?, email = ?, address = ?, gender = ?, 
                    appointment_date = ?, message = ?, status = ?, updated_at = NOW()
                WHERE appointment_id = ? AND user_id = ?
            ");
            $update->bind_param(
                "ssssssssii", 
                $patient_name, $phone, $email, $address, $gender, 
                $appointment_date, $message, $status, $appointment_id, $user_id
            );

            if ($update->execute()) {
                header("Location: my_appointments.php?update=success");
                exit();
            } else {
                $error = "Failed to update appointment. Please try again.";
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
<title>Edit Appointment | SwasthyaTrack</title>
<link rel="stylesheet" href="../css/patient-dashboard.css">

<style>
body {
    background: #f4f8ff;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
}
.container {
    width: 100%;
    max-width: 700px;
    margin: 60px auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}
h1 {
    color: #015eac;
    margin-bottom: 25px;
    font-size: 1.8rem;
    border-bottom: 3px solid #015eac;
    padding-bottom: 10px;
}
.form-group {
    margin-bottom: 20px;
}
label {
    font-weight: bold;
    color: #015eac;
    display: block;
    margin-bottom: 6px;
}
input, textarea, select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
}
textarea {
    resize: vertical;
    min-height: 80px;
}
.btn-container {
    display: flex;
    justify-content: space-between;
    margin-top: 25px;
}
.btn {
    padding: 10px 18px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}
.btn-primary {
    background: #015eac;
    color: #fff;
}
.btn-primary:hover {
    background: #004d91;
}
.btn-secondary {
    background: #e0e0e0;
    color: #333;
}
.btn-secondary:hover {
    background: #cacaca;
}
.message {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
    font-weight: bold;
    text-align: center;
}
.error {
    background: #ffe6e6;
    color: #c0392b;
}
.disabled-field {
    background: #f5f5f5;
    color: #777;
}
</style>
</head>

<body>
<header>
    <div class="logo">
        <a href="home.php">
            <img class="nav-img" src="../images/nav-logo.png" alt="">
            <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
        </a>
    </div>
    <nav>
        <a href="my_appointments.php" class="btn-login">Back</a>
        <a href="../auth/logout.php" class="btn-login">Logout</a>
    </nav>
</header>

<div class="container">
    <h1>Edit Appointment</h1>

    <?php if (isset($error)): ?>
        <div class="message error"><?= safe_html($error) ?></div>
    <?php endif; ?>

    <?php
    $isDisabled = in_array($appointment['status'], ['Cancelled', 'Completed']);
    ?>

    <form method="POST" onsubmit="return confirmUpdate();">
        <div class="form-group">
            <label>Doctor</label>
            <input type="text" value="<?= safe_html($appointment['doctor_name']) ?>" disabled>
        </div>

        <div class="form-group">
            <label>Patient Name</label>
            <input type="text" name="patient_name" value="<?= safe_html($appointment['patient_name']) ?>" required <?= $isDisabled ? 'disabled class="disabled-field"' : '' ?>>
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?= safe_html($appointment['phone']) ?>" required <?= $isDisabled ? 'disabled class="disabled-field"' : '' ?>>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= safe_html($appointment['email']) ?>" required <?= $isDisabled ? 'disabled class="disabled-field"' : '' ?>>
        </div>

        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" value="<?= safe_html($appointment['address']) ?>" <?= $isDisabled ? 'disabled class="disabled-field"' : '' ?>>
        </div>

        <div class="form-group">
            <label>Gender</label>
            <select name="gender" required <?= $isDisabled ? 'disabled class="disabled-field"' : '' ?>>
                <option value="male" <?= $appointment['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                <option value="female" <?= $appointment['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                <option value="other" <?= $appointment['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label>Appointment Date</label>
            <input type="date" name="appointment_date" 
                   value="<?= safe_html(substr($appointment['appointment_date'], 0, 10)) ?>" required <?= $isDisabled ? 'disabled class="disabled-field"' : '' ?>>
        </div>

        <div class="form-group">
            <label>Your Message / Note</label>
            <textarea name="message" placeholder="Add or update your message" <?= $isDisabled ? 'disabled class="disabled-field"' : '' ?>><?= safe_html($appointment['message']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status" <?= $isDisabled ? 'disabled class="disabled-field"' : '' ?>>
                <option value="Booked" <?= $appointment['status'] === 'Booked' ? 'selected' : '' ?>>Booked</option>
                <option value="Cancelled" <?= $appointment['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                <option value="Completed" <?= $appointment['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
            </select>
        </div>

        <div class="btn-container">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='my_appointments.php'">Back</button>
            <button type="submit" class="btn btn-primary" <?= $isDisabled ? 'disabled' : '' ?>>Save Changes</button>
        </div>
    </form>
</div>

<footer>
    <p>© 2025 SwasthyaTrack. All Rights Reserved.</p>
</footer>

<script>
function confirmUpdate() {
    return confirm("Do you want to update this appointment?");
}
</script>

</body>
</html>
