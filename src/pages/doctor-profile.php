<?php
// Include the database connection file
include("../config/db.php");

// ------------------------------
// Validate doctor_id from URL
// ------------------------------

// Check if doctor_id is missing
if (!isset($_GET['doctor_id'])) {
    http_response_code(400); // Bad Request
    exit("Doctor ID is required.");
}

// Check if doctor_id is not a number
if (!is_numeric($_GET['doctor_id'])) {
    http_response_code(400); // Bad Request
    exit("Invalid doctor ID.");
}

// Convert doctor_id safely to integer
$doctor_id = (int) $_GET['doctor_id'];


// ------------------------------
// Fetch doctor info from database
// ------------------------------
$stmt = $conn->prepare("
    SELECT doctor_id, name, email, department, designation, council_number, phone, photo
    FROM doctors
    WHERE doctor_id = ?
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();
$stmt->close();

// If no doctor found
if (!$doctor) {
    http_response_code(404); // Not Found
    exit("Doctor not found.");
}


// ------------------------------
// Fetch doctor education details
// ------------------------------
$edu_stmt = $conn->prepare("
    SELECT degree, institution, year_of_completion
    FROM doctor_education
    WHERE doctor_id = ?
    ORDER BY year_of_completion
");
$edu_stmt->bind_param("i", $doctor_id);
$edu_stmt->execute();
$education = $edu_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$edu_stmt->close();


// ------------------------------
// Helper function for escaping output (XSS safe)
// ------------------------------
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}


// ------------------------------
// Determine doctor photo path
// ------------------------------
$photoPath = "../images/doctor.png"; // Default photo if none provided

if (!empty($doctor['photo'])) {
    $photoPath = "../images/doctors/" . e($doctor['photo']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dr. <?php echo e($doctor['name']); ?> — Profile</title>
  <link rel="stylesheet" href="../css/doctor-profile.css"/>
</head>
<body>

  <!-- Header -->
  <header>
    <div class="logo">
      <a href="#">
        <img class="nav-img" src="../images/nav-logo.png" alt="Logo">
        <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
      </a>
    </div>
    <nav>
      <a href="../auth/logout.php" class="btn-login">Logout</a>
    </nav>
  </header>

  <!-- Main Profile Section -->
  <main>
    <div class="profile-wrapper">
      <div class="profile-content">

        <!-- Left Column (Photo + Name) -->
        <section class="left-col">
          <div class="photo-card">
            <img class="photo" src="<?php echo $photoPath; ?>" alt="Doctor photo">
            <div class="name-card">
              <div class="name">Dr. <?php echo e($doctor['name']); ?></div>

              <!-- Show designation if available, otherwise show placeholder -->
              <?php
              if (!empty($doctor['designation'])) {
                  echo '<div class="designation-text">' . e($doctor['designation']) . '</div>';
              } else {
                  echo '<div class="designation-text">Designation</div>';
              }
              ?>
            </div>
          </div>
        </section>

        <!-- Right Column (Details) -->
        <section class="right-col">

          <!-- Department -->
          <div class="info-block">
            <div class="label">Department:</div>
            <div class="value">
              <?php
              if (!empty($doctor['department'])) {
                  echo e($doctor['department']);
              } else {
                  echo "Not specified";
              }
              ?>
            </div>
          </div>

          <!-- Designation -->
          <div class="info-block">
            <div class="label">Designation:</div>
            <div class="value">
              <?php
              if (!empty($doctor['designation'])) {
                  echo e($doctor['designation']);
              } else {
                  echo "Not specified";
              }
              ?>
            </div>
          </div>

          <!-- Education -->
          <div class="info-block">
            <div class="label">Education:</div>
            <div class="value">
              <?php
              if (!empty($education)) {
                  foreach ($education as $edu) {
                      echo e($edu['degree']) . ", " . e($edu['institution']) . " (" . e($edu['year_of_completion']) . ")<br>";
                  }
              } else {
                  echo "No education records.";
              }
              ?>
            </div>
          </div>

          <!-- Council Number -->
          <div class="info-block">
            <div class="label">Council No:</div>
            <div class="value">
              <?php
              if (!empty($doctor['council_number'])) {
                  echo e($doctor['council_number']);
              } else {
                  echo "Not specified";
              }
              ?>
            </div>
          </div>

          <!-- Email -->
          <div class="info-block">
            <div class="label">Email:</div>
            <div class="value">
              <?php
              if (!empty($doctor['email'])) {
                  echo e($doctor['email']);
              } else {
                  echo "Not specified";
              }
              ?>
            </div>
          </div>

          <!-- Phone -->
          <div class="info-block">
            <div class="label">Phone:</div>
            <div class="value">
              <?php
              if (!empty($doctor['phone'])) {
                  echo e($doctor['phone']);
              } else {
                  echo "Not specified";
              }
              ?>
            </div>
          </div>

        </section>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer>
    <p>© 2025 SwasthyaTrack. All Rights Reserved.</p>
  </footer>
</body>
</html>
