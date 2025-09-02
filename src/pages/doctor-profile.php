<?php
include("../config/db.php"); // Include database connection file

// Validate doctor_id from URL

if (!isset($_GET['doctor_id'])) {
    // doctor_id not provided
    http_response_code(400);
    exit("Doctor ID is required.");
}

if (!is_numeric($_GET['doctor_id'])) {
    // doctor_id is not a number
    http_response_code(400);
    exit("Invalid doctor ID.");
}

$doctor_id = (int) $_GET['doctor_id']; // Convert doctor_id safely to integer

// Fetch doctor info (with photo)

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

if (!$doctor) {
    // No doctor found for this ID
    http_response_code(404);
    exit("Doctor not found.");
}

// Fetch doctor education details
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


// Helper function for escaping output (XSS safe)
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Determine doctor photo path
$photoPath = "../images/doctor.png"; // Default photo

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

              <?php if (!empty($doctor['designation'])): ?>
                <div class="designation-text"><?php echo e($doctor['designation']); ?></div>
              <?php else: ?>
                <div class="designation-text">Designation</div>
              <?php endif; ?>
            </div>
          </div>
        </section>

        <!-- Right Column (Details) -->
        <section class="right-col">

          <!-- Department -->
          <div class="info-block">
            <div class="label">Department:</div>
            <div class="value">
              <?php if (!empty($doctor['department'])): ?>
                <?php echo e($doctor['department']); ?>
              <?php else: ?>
                Not specified
              <?php endif; ?>
            </div>
          </div>

          <!-- Designation -->
          <div class="info-block">
            <div class="label">Designation:</div>
            <div class="value">
              <?php if (!empty($doctor['designation'])): ?>
                <?php echo e($doctor['designation']); ?>
              <?php else: ?>
                Not specified
              <?php endif; ?>
            </div>
          </div>

          <!-- Education -->
          <div class="info-block">
            <div class="label">Education:</div>
            <div class="value">
              <?php if ($education): ?>
                <?php foreach ($education as $edu): ?>
                  <?php echo e($edu['degree']); ?>,
                  <?php echo e($edu['institution']); ?>
                  (<?php echo e($edu['year_of_completion']); ?>)
                  <br>
                <?php endforeach; ?>
              <?php else: ?>
                No education records.
              <?php endif; ?>
            </div>
          </div>

          <!-- Council Number -->
          <div class="info-block">
            <div class="label">Council No:</div>
            <div class="value">
              <?php if (!empty($doctor['council_number'])): ?>
                <?php echo e($doctor['council_number']); ?>
              <?php else: ?>
                Not specified
              <?php endif; ?>
            </div>
          </div>

          <!-- Email -->
          <div class="info-block">
            <div class="label">Email:</div>
            <div class="value">
              <?php if (!empty($doctor['email'])): ?>
                <?php echo e($doctor['email']); ?>
              <?php else: ?>
                Not specified
              <?php endif; ?>
            </div>
          </div>

          <!-- Phone -->
          <div class="info-block">
            <div class="label">Phone:</div>
            <div class="value">
              <?php if (!empty($doctor['phone'])): ?>
                <?php echo e($doctor['phone']); ?>
              <?php else: ?>
                Not specified
              <?php endif; ?>
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
