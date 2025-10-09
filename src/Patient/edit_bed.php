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
// VALIDATE BED ID
// ==========================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_beds.php");
    exit();
}

$bed_id = (int) $_GET['id'];

// ==========================
// FETCH BED DATA (must belong to user)
// ==========================
$stmt = $conn->prepare("SELECT * FROM beds WHERE bed_id = ? AND user_id = ?");
$stmt->bind_param("ii", $bed_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_beds.php?error=" . urlencode("Invalid bed or access denied."));
    exit();
}

$bed = $result->fetch_assoc();
$stmt->close();

// ==========================
// HANDLE FORM SUBMISSION
// ==========================
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_name = trim($_POST['patient_name'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $reason_for_admission = trim($_POST['reason_for_admission'] ?? '');
    $reserved_date = $_POST['reserved_date'] ?? '';

    // Validation
    if ($patient_name === '' || $contact === '' || $gender === '' || $reserved_date === '') {
        $error = "All required fields must be filled.";
    } elseif (!preg_match("/^[0-9+\-\s]{7,15}$/", $contact)) {
        $error = "Invalid contact number format.";
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    }

    if (!$error) {
        $stmt = $conn->prepare("
            UPDATE beds
            SET patient_name = ?, gender = ?, contact = ?, email = ?, address = ?, 
                reason_for_admission = ?, reserved_date = ?
            WHERE bed_id = ? AND user_id = ?
        ");
        $stmt->bind_param(
            "sssssssii",
            $patient_name,
            $gender,
            $contact,
            $email,
            $address,
            $reason_for_admission,
            $reserved_date,
            $bed_id,
            $user_id
        );

        if ($stmt->execute()) {
            header("Location: my_bed_bookings.php?success=" . urlencode("Bed details updated successfully."));
            exit();
        } else {
            $error = "Database update failed: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Escape function
function e($val) {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Bed Booking | Patient Panel</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Segoe UI', Roboto, sans-serif;
  background: #edf4fb;
  color: #0b1e2d;
}

.container {
  max-width: 650px;
  margin: 3rem auto;
  background: #fff;
  padding: 2rem 2.5rem;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}

h2 {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #015eac;
  font-size: 1.7rem;
  font-weight: 700;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

label {
  font-weight: 600;
  margin-bottom: 6px;
}

input, select, textarea {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 0.95rem;
}

button {
  background: #015eac;
  color: #fff;
  font-weight: 600;
  padding: 0.9rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  width: 100%;
  margin-top: 1rem;
}

button:hover { background: #004d91; }

.error {
  color: red;
  text-align: center;
  margin-bottom: 10px;
}
</style>
</head>
<body>

<div class="container">
  <h2>Edit Bed Booking</h2>

  <?php if (!empty($error)): ?>
    <p class="error"><?= e($error) ?></p>
  <?php endif; ?>

  <form method="POST" class="form-grid" onsubmit="return validateForm();">
    <div class="form-group">
      <label>Patient Name *</label>
      <input type="text" name="patient_name" value="<?= e($bed['patient_name']) ?>" required>
    </div>

    <div class="form-group">
      <label>Gender *</label>
      <select name="gender" required>
        <option value="">Select</option>
        <option value="male" <?= $bed['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
        <option value="female" <?= $bed['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
        <option value="other" <?= $bed['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
      </select>
    </div>

    <div class="form-group">
      <label>Contact *</label>
      <input type="text" name="contact" value="<?= e($bed['contact']) ?>" required>
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?= e($bed['email']) ?>">
    </div>

    <div class="form-group">
      <label>Address</label>
      <input type="text" name="address" value="<?= e($bed['address']) ?>">
    </div>

    <div class="form-group">
      <label>Reason for Admission</label>
      <textarea name="reason_for_admission" rows="3"><?= e($bed['reason_for_admission']) ?></textarea>
    </div>

    <div class="form-group">
      <label>Reserved Date *</label>
      <input type="date" name="reserved_date" value="<?= e($bed['reserved_date']) ?>" required>
    </div>

    <button type="submit">Save Changes</button>
  </form>
</div>

<script>
function validateForm() {
  const contactPattern = /^[0-9+\-\s]{7,15}$/;
  const form = document.forms[0];
  const contact = form.contact.value.trim();
  const email = form.email.value.trim();

  if (!contactPattern.test(contact)) {
    alert("Invalid contact number format.");
    return false;
  }

  if (email && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
    alert("Invalid email address.");
    return false;
  }

  return true;
}
</script>

</body>
</html>
