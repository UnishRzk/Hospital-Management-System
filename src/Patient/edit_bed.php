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
    if ($patient_name === '' || $contact === '' || $gender === '' || $reserved_date === '' || $reason_for_admission === '') {
        $error = "All required fields must be filled.";
    } elseif (!preg_match("/^[0-9+\-\s]{7,15}$/", $contact)) {
        $error = "Invalid contact number format.";
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strtotime($reserved_date) < strtotime(date('Y-m-d'))) {
        $error = "Reserved date cannot be in the past.";
    } elseif (strlen($reason_for_admission) < 10) {
        $error = "Reason for admission must be at least 10 characters long.";
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

// Get today's date for min attribute
$today = date('Y-m-d');
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

label span.required {
  color: #e74c3c;
  margin-left: 3px;
}

input, select, textarea {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 0.95rem;
  transition: border-color 0.3s;
}

input:focus, select:focus, textarea:focus {
  outline: none;
  border-color: #015eac;
  box-shadow: 0 0 0 2px rgba(1, 94, 172, 0.1);
}

input.invalid, select.invalid, textarea.invalid {
  border-color: #e74c3c;
}

.error {
  color: #e74c3c;
  text-align: center;
  margin-bottom: 10px;
  padding: 10px;
  background-color: #fde8e8;
  border-radius: 6px;
  border-left: 4px solid #e74c3c;
}

.error-field {
  color: #e74c3c;
  font-size: 0.85rem;
  margin-top: 4px;
  display: none;
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
  transition: background 0.3s;
}

button:hover { background: #004d91; }

.success-message {
  color: #27ae60;
  text-align: center;
  margin-bottom: 10px;
  padding: 10px;
  background-color: #e9f7ef;
  border-radius: 6px;
  border-left: 4px solid #27ae60;
}

.info-text {
  color: #7f8c8d;
  font-size: 0.9rem;
  margin-top: 4px;
}

.character-count {
  font-size: 0.85rem;
  text-align: right;
  margin-top: 4px;
  color: #7f8c8d;
}

.character-count.warning {
  color: #f39c12;
}

.character-count.error {
  color: #e74c3c;
}
</style>
</head>
<body>

<div class="container">
  <h2>Edit Bed Booking</h2>

  <?php if (!empty($error)): ?>
    <p class="error"><?= e($error) ?></p>
  <?php endif; ?>

  <?php if (isset($_GET['success'])): ?>
    <p class="success-message"><?= e(urldecode($_GET['success'])) ?></p>
  <?php endif; ?>

  <form method="POST" class="form-grid" id="bookingForm" novalidate>
    <div class="form-group">
      <label>Patient Name <span class="required">*</span></label>
      <input type="text" name="patient_name" id="patient_name" value="<?= e($bed['patient_name']) ?>" required>
      <div class="error-field" id="patient_name_error">Please enter a valid patient name (minimum 2 characters)</div>
    </div>

    <div class="form-group">
      <label>Gender <span class="required">*</span></label>
      <select name="gender" id="gender" required>
        <option value="">Select Gender</option>
        <option value="male" <?= $bed['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
        <option value="female" <?= $bed['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
        <option value="other" <?= $bed['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
      </select>
      <div class="error-field" id="gender_error">Please select a gender</div>
    </div>

    <div class="form-group">
      <label>Contact Number <span class="required">*</span></label>
      <input type="text" name="contact" id="contact" value="<?= e($bed['contact']) ?>" required>
      <div class="info-text">Format: 10-15 digits, may include +, -, or spaces</div>
      <div class="error-field" id="contact_error">Please enter a valid contact number (10-15 digits)</div>
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" id="email" value="<?= e($bed['email']) ?>">
      <div class="error-field" id="email_error">Please enter a valid email address</div>
    </div>

    <div class="form-group">
      <label>Address</label>
      <input type="text" name="address" id="address" value="<?= e($bed['address']) ?>">
    </div>

    <div class="form-group">
      <label>Reason for Admission <span class="required">*</span></label>
      <textarea name="reason_for_admission" id="reason_for_admission" rows="3" required><?= e($bed['reason_for_admission']) ?></textarea>
      <div class="character-count" id="reason_counter">Minimum 10 characters required</div>
      <div class="error-field" id="reason_for_admission_error">Please provide a detailed reason for admission (minimum 10 characters)</div>
    </div>

    <div class="form-group">
      <label>Reserved Date <span class="required">*</span></label>
      <input type="date" name="reserved_date" id="reserved_date" value="<?= e($bed['reserved_date']) ?>" min="<?= $today ?>" required>
      <div class="info-text">Date cannot be in the past</div>
      <div class="error-field" id="reserved_date_error">Please select a valid future date</div>
    </div>

    <button type="submit" id="submitBtn">Save Changes</button>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('bookingForm');
  const today = new Date().toISOString().split('T')[0];
  
  // Set min date for reserved date input
  const reservedDateInput = document.getElementById('reserved_date');
  reservedDateInput.min = today;
  
  // Validation patterns
  const patterns = {
    contact: /^[\d+\-\s]{10,15}$/,
    email: /^[^@\s]+@[^@\s]+\.[^@\s]+$/,
    patientName: /^[A-Za-z\s]{2,50}$/
  };
  
  // Show error for a field
  function showError(fieldId, message) {
    const errorElement = document.getElementById(fieldId + '_error');
    const inputElement = document.getElementById(fieldId);
    
    if (errorElement) {
      errorElement.textContent = message;
      errorElement.style.display = 'block';
    }
    
    if (inputElement) {
      inputElement.classList.add('invalid');
    }
  }
  
  // Hide error for a field
  function hideError(fieldId) {
    const errorElement = document.getElementById(fieldId + '_error');
    const inputElement = document.getElementById(fieldId);
    
    if (errorElement) {
      errorElement.style.display = 'none';
    }
    
    if (inputElement) {
      inputElement.classList.remove('invalid');
    }
  }
  
  // Validate patient name
  function validatePatientName() {
    const name = document.getElementById('patient_name').value.trim();
    if (name.length < 2) {
      showError('patient_name', 'Patient name must be at least 2 characters long');
      return false;
    }
    hideError('patient_name');
    return true;
  }
  
  // Validate gender
  function validateGender() {
    const gender = document.getElementById('gender').value;
    if (!gender) {
      showError('gender', 'Please select a gender');
      return false;
    }
    hideError('gender');
    return true;
  }
  
  // Validate contact number
  function validateContact() {
    const contact = document.getElementById('contact').value.trim();
    if (!patterns.contact.test(contact)) {
      showError('contact', 'Please enter a valid contact number (10-15 digits, may include +, -, or spaces)');
      return false;
    }
    hideError('contact');
    return true;
  }
  
  // Validate email (optional field)
  function validateEmail() {
    const email = document.getElementById('email').value.trim();
    if (email && !patterns.email.test(email)) {
      showError('email', 'Please enter a valid email address');
      return false;
    }
    hideError('email');
    return true;
  }
  
  // Validate reason for admission
  function validateReasonForAdmission() {
    const reason = document.getElementById('reason_for_admission').value.trim();
    const reasonCounter = document.getElementById('reason_counter');
    
    if (reason.length === 0) {
      showError('reason_for_admission', 'Please provide a reason for admission');
      reasonCounter.textContent = 'Required: 0/10 characters';
      reasonCounter.className = 'character-count error';
      return false;
    }
    
    if (reason.length < 10) {
      showError('reason_for_admission', 'Reason for admission must be at least 10 characters long');
      reasonCounter.textContent = `Minimum not met: ${reason.length}/10 characters`;
      reasonCounter.className = 'character-count error';
      return false;
    }
    
    // Update character counter
    if (reason.length < 20) {
      reasonCounter.textContent = `Minimum met: ${reason.length} characters (more details recommended)`;
      reasonCounter.className = 'character-count warning';
    } else if (reason.length > 500) {
      reasonCounter.textContent = `Maximum length: ${reason.length}/500 characters`;
      reasonCounter.className = 'character-count error';
    } else {
      reasonCounter.textContent = `${reason.length} characters`;
      reasonCounter.className = 'character-count';
    }
    
    hideError('reason_for_admission');
    return true;
  }
  
  // Validate reserved date
  function validateReservedDate() {
    const dateInput = document.getElementById('reserved_date');
    const selectedDate = new Date(dateInput.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (!dateInput.value) {
      showError('reserved_date', 'Please select a reservation date');
      return false;
    }
    
    if (selectedDate < today) {
      showError('reserved_date', 'Reservation date cannot be in the past');
      return false;
    }
    
    hideError('reserved_date');
    return true;
  }
  
  // Update character counter for reason textarea
  function updateReasonCounter() {
    const reason = document.getElementById('reason_for_admission').value.trim();
    const reasonCounter = document.getElementById('reason_counter');
    
    if (reason.length === 0) {
      reasonCounter.textContent = 'Minimum 10 characters required';
      reasonCounter.className = 'character-count';
    } else if (reason.length < 10) {
      reasonCounter.textContent = `Minimum not met: ${reason.length}/10 characters`;
      reasonCounter.className = 'character-count error';
    } else if (reason.length < 20) {
      reasonCounter.textContent = `Minimum met: ${reason.length} characters (more details recommended)`;
      reasonCounter.className = 'character-count warning';
    } else if (reason.length > 500) {
      reasonCounter.textContent = `Maximum length: ${reason.length}/500 characters`;
      reasonCounter.className = 'character-count error';
    } else {
      reasonCounter.textContent = `${reason.length} characters`;
      reasonCounter.className = 'character-count';
    }
  }
  
  // Validate all fields
  function validateForm() {
    const isNameValid = validatePatientName();
    const isGenderValid = validateGender();
    const isContactValid = validateContact();
    const isEmailValid = validateEmail();
    const isReasonValid = validateReasonForAdmission();
    const isDateValid = validateReservedDate();
    
    return isNameValid && isGenderValid && isContactValid && isEmailValid && isReasonValid && isDateValid;
  }
  
  // Real-time validation on input
  document.getElementById('patient_name').addEventListener('blur', validatePatientName);
  document.getElementById('gender').addEventListener('change', validateGender);
  document.getElementById('contact').addEventListener('blur', validateContact);
  document.getElementById('email').addEventListener('blur', validateEmail);
  document.getElementById('reason_for_admission').addEventListener('input', updateReasonCounter);
  document.getElementById('reason_for_admission').addEventListener('blur', validateReasonForAdmission);
  document.getElementById('reserved_date').addEventListener('change', validateReservedDate);
  
  // Clear validation on focus
  const inputs = form.querySelectorAll('input, select, textarea');
  inputs.forEach(input => {
    input.addEventListener('focus', function() {
      hideError(this.id);
    });
  });
  
  // Form submission handler
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (validateForm()) {
      // If validation passes, submit the form
      this.submit();
    } else {
      // Scroll to first error
      const firstError = document.querySelector('.error-field[style*="display: block"]');
      if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  });
  
  // Prevent past date selection via keyboard
  reservedDateInput.addEventListener('keydown', function(e) {
    e.preventDefault();
  });
  
  // Initialize validation on page load for existing values
  validatePatientName();
  validateGender();
  validateContact();
  validateEmail();
  updateReasonCounter(); // Initialize the character counter
  validateReasonForAdmission();
  validateReservedDate();
});
</script>

</body>
</html>