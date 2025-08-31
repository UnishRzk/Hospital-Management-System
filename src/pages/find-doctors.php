<?php
include("../config/db.php");

// Fetch doctors
$result = $conn->query("SELECT name, designation FROM doctors");
$doctors = [];
while($row = $result->fetch_assoc()) {
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
</head>
<body>
  <header>
    <div class="logo">
      <a href="#">
        <img class="nav-img" src="../images/nav-logo.png" alt="">
        <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
      </a>
    </div>
    <nav>
      <a href="../auth/logout.php" class="btn-login">Logout</a>
    </nav>
  </header>

  <main class="doctor-section">
    <h2>Meet Our Doctors</h2>
    <div class="doctor-grid" id="doctorGrid"></div>
  </main>

  <footer>
    <p>© 2025 SwasthyaTrack. All Rights Reserved.</p>
  </footer>

  <script>
    const doctors = <?php echo json_encode($doctors); ?>;
    const container = document.getElementById("doctorGrid");

    doctors.forEach((doc, i) => {
      const card = document.createElement("div");
      card.className = "doctor-card";
      card.style.animationDelay = `${i * 0.1}s`;
      card.innerHTML = `
        <img src="../images/doctor.png" alt="Doctor">
        <h3>Dr. ${doc.name}</h3>
        <p>${doc.designation || "Not specified"}</p>
        <a href="#">View Profile</a>
      `;
      container.appendChild(card);
    });
  </script>
</body>
</html>
