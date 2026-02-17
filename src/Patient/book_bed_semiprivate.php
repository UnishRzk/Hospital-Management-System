<?php
session_start();
include("../config/db.php"); // Connect to MySQL database

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Logged-in user's ID
$success = "";
$error = "";


$sql = "SELECT bed_id FROM beds WHERE status = 'Empty' AND type = 'Semi-Private'";
$result = $conn->query($sql);

$available_beds = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $available_beds[] = $row['bed_id'];
    }
}

$form_data = [
    'bed_id' => '', 'patient_name' => '', 'gender' => '',
    'contact' => '', 'email' => '', 'address' => '',
    'reason_for_admission' => '', 'reserved_date' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Populate form_data and trim
    $form_data['bed_id'] = $_POST['bed_id'] ?? '';
    $form_data['patient_name'] = trim($_POST['patient_name'] ?? '');
    $form_data['gender'] = $_POST['gender'] ?? '';
    $form_data['contact'] = trim($_POST['contact'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $form_data['address'] = trim($_POST['address'] ?? '');
    $form_data['reason_for_admission'] = trim($_POST['reason_for_admission'] ?? '');
    $form_data['reserved_date'] = $_POST['reserved_date'] ?? '';

    // Extract variables for easier use in DB query
    extract($form_data);

    // Validate input
    if (!$bed_id || !$patient_name || !$gender || !$contact || !$reserved_date || !$reason_for_admission) {
         $error = "";
    } elseif (!preg_match('/^[0-9]{7,15}$/', $contact)) {
        $error = "Invalid contact number format. Use 7 to 15 digits.";
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address format.";
    }
    else {
  
        $check_sql = "SELECT bed_id FROM beds WHERE bed_id = ? AND status = 'Empty' AND type = 'Semi-Private'";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("i", $bed_id);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows === 1) {
            $update_sql = "UPDATE beds 
                            SET user_id = ?, patient_name = ?, gender = ?, contact = ?, email = ?, address = ?, 
                                reason_for_admission = ?, reserved_date = ?, status = 'Reserved'
                            WHERE bed_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param(
                "isssssssi",
                $user_id,
                $patient_name,
                $gender,
                $contact,
                $email,
                $address,
                $reason_for_admission,
                $reserved_date,
                $bed_id
            );

            if ($update_stmt->execute()) {
                $success = "Bed successfully booked!";
                $available_beds = [];
            } else {
                $error = "Database update failed. Please try again: " . $conn->error;
            }
        } else {
            $error = "Sorry, the selected bed is no longer available.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Semi-Private Bed Booking — SwasthyaTrack</title>
<link rel="stylesheet" href="../css/book-bed.css"/>
</head>
<body>

<header>
  <div class="logo">
    <a href="patient_dashboard.php">
      <img class="nav-img" src="../images/nav-logo.png" alt="Logo">
      <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
    </a>
  </div>
  <nav>
    <a href="bed_type.php" class="btn-login">Back</a>
  </nav>
</header>

<div class="form-container">
  <h2>Semi-Private Bed Reservation</h2>

  <?php if ($success): ?>
      <div class="alert-success"><?= e("✅ " . $success) ?></div>
  <?php elseif ($error): ?>
      <div class="alert-error"><?= e("❌ " . $error) ?></div>
  <?php endif; ?>

  <?php if (empty($available_beds)): ?>
    <p class="alert-error">🚫 Sorry, no **Semi-Private** beds are available for booking at the moment. Please check again later.</p>
  <?php else: ?>

  <form id="bedBookingForm" method="POST">
      <div class="form-grid">
          
          <div class="form-group">
              <label for="bed_id">Select Bed *</label>
              <select id="bed_id" name="bed_id" required>
                  <option value="">-- Choose a Bed --</option>
                  <?php foreach ($available_beds as $bed_id): ?>
                      <option 
                          value="<?= e($bed_id) ?>"
                          <?= (e($form_data['bed_id']) === e($bed_id)) ? 'selected' : '' ?>
                      >
                          Bed #<?= e($bed_id) ?>
                      </option>
                  <?php endforeach; ?>
              </select>
              <span class="error"></span>
          </div>

          <div class="form-group">
              <label for="patient_name">Patient Name *</label>
              <input id="patient_name" type="text" name="patient_name" required value="<?= e($form_data['patient_name']) ?>">
              <span class="error"></span>
          </div>

          <div class="form-group">
              <label for="gender">Gender *</label>
              <select id="gender" name="gender" required>
                  <option value="">-- Select Gender --</option>
                  <option value="male" <?= (e($form_data['gender']) === 'male') ? 'selected' : '' ?>>Male</option>
                  <option value="female" <?= (e($form_data['gender']) === 'female') ? 'selected' : '' ?>>Female</option>
                  <option value="other" <?= (e($form_data['gender']) === 'other') ? 'selected' : '' ?>>Other</option>
              </select>
              <span class="error"></span>
          </div>

          <div class="form-group">
              <label for="contact">Contact Number *</label>
              <input id="contact" type="tel" name="contact" required pattern="^[0-9]{7,15}$" placeholder="7-15 digits" value="<?= e($form_data['contact']) ?>">
              <span class="error"></span>
          </div>

          <div class="form-group">
              <label for="email">Email</label>
              <input id="email" type="email" name="email" value="<?= e($form_data['email']) ?>">
              <span class="error"></span>
          </div>

          <div class="form-group">
              <label for="reserved_date">Reservation Date *</label>
              <input id="reserved_date" type="date" name="reserved_date" required value="<?= e($form_data['reserved_date']) ?>">
              <span class="error"></span>
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
              <label for="address">Address</label>
              <input id="address" type="text" name="address" value="<?= e($form_data['address']) ?>">
              <span class="error"></span>
          </div>
      </div>
      
      <div class="form-group" style="margin-top:1rem;">
          <label for="reason_for_admission">Reason for Admission *</label>
          <textarea id="reason_for_admission" name="reason_for_admission" rows="3" required placeholder="Briefly state the reason the patient needs admission..."><?= e($form_data['reason_for_admission']) ?></textarea>
          <span class="error"></span>
      </div>

      <button type="submit" class="btn">Confirm Bed Reservation</button>
  </form>
  <?php endif; ?>
</div>

<div class="modal" id="successModal">
  <div class="modal-content">
    <h3>Reservation Confirmed!</h3>
      <p>Your **Semi-Private** bed has been successfully reserved. We will contact you with admission details shortly.</p>
      <button id="closeModal">OK</button>
  </div>
</div>

<footer>
  <p>© 2026 SwasthyaTrack. All Rights Reserved.</p>
</footer>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Set minimum date to today for reservation date
    const dateInput = document.getElementById('reserved_date');
    if (dateInput) {
        dateInput.min = new Date().toISOString().split("T")[0];
    }

    // Function to handle client-side validation
    const validateField = (field) => {
        const errorSpan = field.parentNode.querySelector('.error');
        if (!errorSpan) return;

        errorSpan.textContent = ""; // Clear existing error

        if (field.hasAttribute('required') && !field.value.trim()) {
            errorSpan.textContent = "This field is required.";
        } else if (field.name === 'contact' && field.value.trim() && !/^[0-9]{7,15}$/.test(field.value)) {
            errorSpan.textContent = "Enter a valid contact number (7-15 digits).";
        } else if (field.name === 'email' && field.value.trim() && !field.checkValidity()) {
             // Use browser's built-in email validation logic
            errorSpan.textContent = "Enter a valid email address.";
        }
    };

    // Attach validation listeners to all input fields
    document.querySelectorAll('#bedBookingForm input, #bedBookingForm select, #bedBookingForm textarea').forEach(field => {
        field.addEventListener('input', () => validateField(field));
        field.addEventListener('blur', () => validateField(field));
    });

    // Show success modal if PHP processing was successful
    <?php if ($success): ?>
      document.getElementById('successModal').classList.add('active');
    <?php endif; ?>

    // Close modal and REDIRECT on click
    document.getElementById('closeModal').addEventListener('click', () => {
        // Redirect to the bed type selection page
        window.location.href = './bed_type.php'; 
    });
});
</script>

</body>
</html>