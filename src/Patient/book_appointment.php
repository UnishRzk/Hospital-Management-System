<?php
session_start();
include("../config/db.php");

// Restrict to logged-in patients
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch doctors
$result = $conn->query("SELECT doctor_id, name, designation, photo FROM doctors ORDER BY name ASC");

if (!$result) {
    die("Database error: " . $conn->error);
}

$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meet Our Doctors | SwasthyaTrack</title>
  <link rel="stylesheet" href="../css/find-doctors.css">
  <style>
    /* Optional small UX enhancements */
    .doctor-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .doctor-card:hover {
      transform: scale(1.03);
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <a href="patient_dashboard.php">
        <img class="nav-img" src="../images/nav-logo.png" alt="SwasthyaTrack Logo">
        <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
      </a>
    </div>
    <nav>
        <a href="patient_dashboard.php">Home</a>
        <!-- <a href="#About">Book Appointment</a> -->
        <a href="my_appointments.php">Appointments</a>
        <a href="bed_type.php">Book Bed</a>
        <!-- <a href="#About">Bed Reservations</a> -->
        <!-- <a href="my_prescriptions.php">Prescriptions</a> -->
        <a href="my_reports.php">Reports</a>
      <a href="patient_dashboard.php" class="btn-login">Back</a>
    </nav>
  </header>

  <main class="doctor-section">
    <h2>Meet Our Doctors</h2>
    <div class="doctor-grid" id="doctorGrid"></div>
  </main>

  <footer>
    <p>© 2026 SwasthyaTrack. All Rights Reserved.</p>
  </footer>

<script>
  // Doctors data from PHP
  const doctors = <?php echo json_encode($doctors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
  const container = document.getElementById("doctorGrid");

  // Escape HTML to prevent XSS
  const escapeHTML = str => String(str).replace(/[&<>"']/g,
    m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])
  );

  doctors.forEach((doc, i) => {
    const card = document.createElement("article");
    card.className = "doctor-card";
    card.style.animationDelay = `${i * 0.1}s`;

    // Use uploaded photo if exists, otherwise fallback
    const imgPath = doc.photo ? `../images/doctors/${escapeHTML(doc.photo)}` : `../images/doctor.png`;

    card.innerHTML = `
      <img src="${imgPath}" alt="Dr. ${escapeHTML(doc.name)}" loading="lazy">
      <h3>Dr. ${escapeHTML(doc.name)}</h3>
      <p>${escapeHTML(doc.designation || "Not specified")}</p>
      <a href="appointment.php?doctor_id=${encodeURIComponent(doc.doctor_id)}">Book Appointment</a>
    `;
    container.appendChild(card);
  });
</script>
</body>
</html>
