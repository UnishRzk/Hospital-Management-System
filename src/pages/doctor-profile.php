<?php
include("../config/db.php");

// Validate doctor_id
if (!isset($_GET['doctor_id']) || !is_numeric($_GET['doctor_id'])) {
    http_response_code(400);
    exit("Invalid doctor ID.");
}
$doctor_id = (int)$_GET['doctor_id'];

// Fetch doctor info (with photo)
$stmt = $conn->prepare("
    SELECT doctor_id, name, email, department, designation, council_number, phone, photo
    FROM doctors
    WHERE doctor_id=?
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$doctor) {
    http_response_code(404);
    exit("Doctor not found.");
}

// Fetch education details
$edu_stmt = $conn->prepare("
    SELECT degree, institution, year_of_completion
    FROM doctor_education
    WHERE doctor_id=?
    ORDER BY year_of_completion
");
$edu_stmt->bind_param("i", $doctor_id);
$edu_stmt->execute();
$education = $edu_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$edu_stmt->close();

// Helper for escaping
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Determine photo path
$photoPath = !empty($doctor['photo'])
    ? "../images/doctors/" . e($doctor['photo'])
    : "../images/doctor.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dr. <?= e($doctor['name']) ?> — Profile</title>
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

  <!-- Main -->
  <main>
    <div class="profile-wrapper">
      <div class="profile-content">

        <!-- Left Column -->
        <section class="left-col">
          <div class="photo-card">
            <img class="photo" src="<?= $photoPath ?>" alt="Doctor photo">
            <div class="name-card">
              <div class="name">Dr. <?= e($doctor['name']) ?></div>
              <div class="designation-text"><?= e($doctor['designation'] ?: "Designation") ?></div>
            </div>
          </div>
        </section>

        <!-- Right Column -->
        <section class="right-col">
          <div class="info-block">
            <div class="label">Department:</div>
            <div class="value"><?= e($doctor['department'] ?: "Not specified") ?></div>
          </div>

          <div class="info-block">
            <div class="label">Designation:</div>
            <div class="value"><?= e($doctor['designation'] ?: "Not specified") ?></div>
          </div>

          <div class="info-block">
            <div class="label">Education:</div>
            <div class="value">
              <?php if ($education): ?>
                <?php foreach ($education as $edu): ?>
                  <?= e($edu['degree']) ?>, <?= e($edu['institution']) ?> (<?= e($edu['year_of_completion']) ?>)<br>
                <?php endforeach; ?>
              <?php else: ?>
                No education records.
              <?php endif; ?>
            </div>
          </div>

          <div class="info-block">
            <div class="label">Council No:</div>
            <div class="value"><?= e($doctor['council_number'] ?: "Not specified") ?></div>
          </div>

          <div class="info-block">
            <div class="label">Email:</div>
            <div class="value"><?= e($doctor['email'] ?: "Not specified") ?></div>
          </div>

          <div class="info-block">
            <div class="label">Phone:</div>
            <div class="value"><?= e($doctor['phone'] ?: "Not specified") ?></div>
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
