<?php
session_start();
include("../config/db.php");

// ==========================
// ACCESS CONTROL
// ==========================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// ==========================
// VALIDATE APPOINTMENT ID
// ==========================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid appointment ID.");
}
$appointment_id = (int)$_GET['id'];

// ==========================
// FETCH APPOINTMENT DATA
// ==========================
$stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    die("Appointment not found.");
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
    $appointment_date = $_POST['appointment_date'] ?? '';
    $status = $_POST['status'] ?? '';
    $comment = trim($_POST['comment'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate required fields
    if (empty($appointment_date) || empty($status)) {
        $error = "Appointment date and status are required.";
    } else {
        // Update appointment record securely
        $update = $conn->prepare("
            UPDATE appointments 
            SET appointment_date = ?, status = ?, comment = ?, message = ?, updated_at = NOW() 
            WHERE appointment_id = ?
        ");
        $update->bind_param("ssssi", $appointment_date, $status, $comment, $message, $appointment_id);

        if ($update->execute()) {
            header("Location: manage_appointments.php?update=success");
            exit();
        } else {
            $error = "Failed to update appointment. Please try again.";
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
<style>
    /* ===== Base Theme ===== */
    * {
        margin: 0; padding: 0; box-sizing: border-box;
        font-family: Arial, sans-serif;
    }
    body {
        background: #f5f7fa;
        color: #333;
        min-height: 100vh;
    }

    .container {
        width: 100%;
        max-width: 700px;
        margin: 50px auto;
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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

    input[type="text"], input[type="date"], textarea, select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
        outline: none;
        transition: 0.3s;
    }

    input:focus, textarea:focus, select:focus {
        border-color: #015eac;
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

    .success {
        background: #e8f8f5;
        color: #27ae60;
    }
</style>
</head>
<body>

<div class="container">
    <h1>Edit Appointment</h1>

    <?php if (isset($error)): ?>
        <div class="message error"><?= safe_html($error) ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return confirmUpdate();">

        <div class="form-group">
            <label>Patient Name</label>
            <input type="text" value="<?= safe_html($appointment['patient_name']) ?>" disabled>
        </div>

        <div class="form-group">
            <label>Gender</label>
            <input type="text" value="<?= safe_html($appointment['gender']) ?>" disabled>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="text" value="<?= safe_html($appointment['email']) ?>" disabled>
        </div>

        <div class="form-group">
            <label>Appointment Date</label>
            <input type="date" name="appointment_date" value="<?= safe_html($appointment['appointment_date']) ?>" required>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status" required>
                <option value="Booked" <?= ($appointment['status'] === 'Booked') ? 'selected' : '' ?>>Booked</option>
                <option value="Completed" <?= ($appointment['status'] === 'Completed') ? 'selected' : '' ?>>Completed</option>
                <option value="Cancelled" <?= ($appointment['status'] === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>

        <div class="form-group">
            <label>Message (Patient’s note)</label>
            <textarea name="message" disabled><?= safe_html($appointment['message']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Doctor’s Comment</label>
            <textarea name="comment" placeholder="Add any notes or updates..." disabled ><?= safe_html($appointment['comment']) ?></textarea>
        </div>

        <div class="btn-container">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='manage_appointments.php'">Cancel</button>
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>
</div>

<script>
function confirmUpdate() {
    return confirm("Are you sure you want to update this appointment?");
}
</script>

</body>
</html>
