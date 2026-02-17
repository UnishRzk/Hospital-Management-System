<?php
session_start();
include("../config/db.php");

// ACCESS CONTROL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// VALIDATE BED ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_beds.php");
    exit();
}

$bed_id = (int) $_GET['id'];

// FETCH EXISTING BED DATA
$stmt = $conn->prepare("SELECT * FROM beds WHERE bed_id = ?");
$stmt->bind_param("i", $bed_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_beds.php");
    exit();
}

$bed = $result->fetch_assoc();
$stmt->close();

// HANDLE FORM SUBMISSION
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $patient_name = trim($_POST['patient_name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $reason_for_admission = trim($_POST['reason_for_admission'] ?? '');
    $reserved_date = $_POST['reserved_date'] ?? null;
    $type = $_POST['type'] ?? '';
    $status = $_POST['status'] ?? '';
    // $gender = !empty($_POST['gender']) ? $_POST['gender'] : $bed['gender'];
    if (!empty($_POST['gender'])) {
    $gender = $_POST['gender'];
    } else {
    $gender = $bed['gender'];
    }

    $valid_genders = ['male', 'female', 'other'];
    $valid_types = ['General', 'Semi-Private', 'Private'];
    $valid_status = ['Empty', 'Reserved', 'Occupied', 'Out of Order'];

    if (!in_array($type, $valid_types, true)) {
        $error = "Invalid bed type selected.";
    } elseif (!in_array($status, $valid_status, true)) {
        $error = "Invalid status selected.";
    } elseif (!empty($gender) && !in_array($gender, $valid_genders, true)) {
        $error = "Invalid gender selected.";
    }

    if (!$error && ($status === 'Reserved' || $status === 'Occupied')) {
        if ($patient_name === '' || $contact === '' || $gender === '' || $reserved_date === '') {
            $error = "Please fill all required patient details.";
        } elseif (!preg_match("/^[0-9+\-\s]{7,15}$/", $contact)) {
            $error = "Invalid contact number format.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && $email !== '') {
            $error = "Invalid email format.";
        }
    }

    // CLEAR ALL PATIENT DETAILS IF STATUS = EMPTY or OUT OF ORDER
    if (!$error && (strtolower($status) === 'empty' || strtolower($status) === 'out of order')) {
        $patient_name = null;
        $contact = null;
        $email = null;
        $address = null;
        $reason_for_admission = null;
        $reserved_date = null;
        $gender = null;
        $user_id = null;
    } else {
        $user_id = $bed['user_id']; // retain if not clearing
    }

    // UPDATE DATABASE
    if (!$error) {
        $stmt = $conn->prepare("
            UPDATE beds 
            SET 
                user_id = ?, 
                patient_name = ?, 
                gender = ?, 
                contact = ?, 
                email = ?, 
                address = ?, 
                reason_for_admission = ?, 
                reserved_date = ?, 
                type = ?, 
                status = ?
            WHERE bed_id = ?
        ");

        $stmt->bind_param(
            "isssssssssi",
            $user_id,
            $patient_name,
            $gender,
            $contact,
            $email,
            $address,
            $reason_for_admission,
            $reserved_date,
            $type,
            $status,
            $bed_id
        );

        if ($stmt->execute()) {
            header("Location: manage_beds.php?success=" . urlencode("Bed details updated successfully."));
            exit();
        } else {
            $error = "Database update failed: " . $stmt->error;
        }

        $stmt->close();
    }
}

function e($val) {
    return htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Bed | Admin Panel</title>
<style>
* { 
  box-sizing: border-box; margin: 0; padding: 0;
}

body {
  font-family: Roboto, Segoe UI, sans-serif;
  background: #cfe1f0;
  color: #0b1e2d;
  line-height: 1.5;
}

.form-container {
  max-width: 650px;
  margin: 3rem auto;
  background: #fff;
  padding: 2rem 2.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

h2 {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #015eac;
  font-size: 1.8rem;
  font-weight: 800;
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

.form-group label {
  font-weight: 600;
  margin-bottom: 6px;
}

.form-group input,
.form-group select,
.form-group textarea {
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

button:hover { background: #014c8c; }

.error {
  color: red;
  text-align: center;
  margin-bottom: 10px;
}

.disabled {
  background: #f2f2f2;
  pointer-events: none;
}
</style>
</head>
<body>

<div class="form-container">
  <h2>Edit Bed Details</h2>

  <?php if (!empty($error)): ?>
    <p class="error"><?php echo e($error); ?></p>
  <?php endif; ?>

  <form method="POST" class="form-grid" id="bedForm" onsubmit="return validateForm();">

    <div class="form-group">
      <label>Patient Name</label>
      <input type="text" name="patient_name" value="<?php echo e($bed['patient_name']); ?>">
    </div>

    <div class="form-group">
      <label>Gender</label>
      <select name="gender">
        <option value="">Select</option>
        <option value="male" <?php echo $bed['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
        <option value="female" <?php echo $bed['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
        <option value="other" <?php echo $bed['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
      </select>
    </div>

    <div class="form-group">
      <label>Contact</label>
      <input type="text" name="contact" value="<?php echo e($bed['contact']); ?>">
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?php echo e($bed['email']); ?>">
    </div>

    <div class="form-group">
      <label>Address</label>
      <input type="text" name="address" value="<?php echo e($bed['address']); ?>">
    </div>

    <div class="form-group">
      <label>Reason for Admission</label>
      <textarea name="reason_for_admission" rows="3"><?php echo e($bed['reason_for_admission']); ?></textarea>
    </div>

    <div class="form-group">
      <label>Reserved Date</label>
      <input type="date" name="reserved_date" value="<?php echo e($bed['reserved_date']); ?>">
    </div>

    <div class="form-group">
      <label>Type</label>
      <select name="type">
        <option value="General" <?php echo $bed['type'] === 'General' ? 'selected' : ''; ?>>General</option>
        <option value="Semi-Private" <?php echo $bed['type'] === 'Semi-Private' ? 'selected' : ''; ?>>Semi-Private</option>
        <option value="Private" <?php echo $bed['type'] === 'Private' ? 'selected' : ''; ?>>Private</option>
      </select>
    </div>

    <div class="form-group">
      <label>Status</label>
      <select name="status" id="statusSelect">
        <option value="Empty" <?php echo $bed['status'] === 'Empty' ? 'selected' : ''; ?>>Empty</option>
        <option value="Reserved" <?php echo $bed['status'] === 'Reserved' ? 'selected' : ''; ?>>Reserved</option>
        <option value="Occupied" <?php echo $bed['status'] === 'Occupied' ? 'selected' : ''; ?>>Occupied</option>
        <option value="Out of Order" <?php echo $bed['status'] === 'Out of Order' ? 'selected' : ''; ?>>Out of Order</option>
      </select>
    </div>

    <button type="submit">Save Changes</button>
  </form>
</div>

<script>
const statusSelect = document.getElementById('statusSelect');
const form = document.getElementById('bedForm');
const patientFields = form.querySelectorAll('input[name="patient_name"], input[name="contact"], input[name="email"], input[name="address"], textarea[name="reason_for_admission"], input[name="reserved_date"], select[name="gender"]');

function togglePatientFields() {
  const status = statusSelect.value.toLowerCase();
  const disable = status === 'empty' || status === 'out of order';
  patientFields.forEach(field => {
    if (disable) field.value = '';
    field.disabled = disable;
    field.classList.toggle('disabled', disable);
  });
}

function validateForm() {
  const status = statusSelect.value.toLowerCase();
  if (status === 'reserved' || status === 'occupied') {
    const name = form.patient_name.value.trim();
    const contact = form.contact.value.trim();
    const gender = form.gender.value.trim();
    const date = form.reserved_date.value;

    if (!name || !contact || !gender || !date) {
      alert("Please fill in all required patient fields before saving.");
      return false;
    }

    const contactPattern = /^[0-9+\-\s]{7,15}$/;
    if (!contactPattern.test(contact)) {
      alert("Invalid contact number format.");
      return false;
    }

    const email = form.email.value.trim();
    if (email && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
      alert("Invalid email format.");
      return false;
    }
  }
  return true;
}

statusSelect.addEventListener('change', togglePatientFields);
togglePatientFields();
</script>

</body>
</html>
