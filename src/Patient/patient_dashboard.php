<?php
session_start();
require_once '../config/db.php'; // adjust this if your DB connection file path differs

// Ensure user is logged in and user_id exists
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = "User"; // Default fallback

// Fetch user's full name from patients table
$query = "SELECT name FROM patients WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    $full_name = trim($row['name']);
    // Extract only the first name
    $name_parts = explode(" ", $full_name);
    $first_name = ucfirst($name_parts[0]);
}

$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>Patient Dashboard — SwasthyaTrack</title>
    <link rel="stylesheet" href="../css/patient-dashboard.css">
</head>
<body>
<header>
    <div class="logo">
        <a href="home.php">
            <img class="nav-img" src="../images/nav-logo.png" alt="">
            <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
        </a>
    </div>
    <nav>
        <a href="../auth/logout.php" class="btn-login">Logout</a>
    </nav>
</header>

<main role="main">

    <!-- Dashboard Top -->
    <section class="dashboard-top">
        <div class="quick-actions">
            <a href="book_appointment.php" class="action-card">
                <div class="icon"><img src="../images/icons/dates.png" alt="calendar"></div>
                <div class="label">Book Appointment</div>
            </a>
            <a href="bed_type.php" class="action-card">
                <div class="icon"><img src="../images/icons/hospital-bed.png" alt="bed"></div>
                <div class="label">Book Bed</div>
            </a>
            <a href="my_appointments.php" class="action-card">
                <div class="icon"><img src="../images/icons/prescription.png" alt="prescriptions"></div>
                <div class="label">My Appointments</div>
            </a>
            <a href="my_bed_bookings.php" class="action-card">
                <div class="icon"><img src="../images/icons/report.png" alt="reports"></div>
                <div class="label">My Reservation</div>
            </a>
            <a href="my_prescriptions.php" class="action-card">
                <div class="icon"><img src="../images/icons/update-report.png" alt="upload"></div>
                <div class="label">My Prescriptions</div>
            </a>
            <a href="my_reports.php" class="action-card">
                <div class="icon"><img src="../images/icons/doctor.png" alt="doctor"></div>
                <div class="label">My Reports</div>
            </a>
        </div>

        <aside class="greeting">
            <img src="../images/veterinarian.png" alt="Doctor illustration">
            <h2>Hi <?php echo htmlspecialchars($first_name); ?>!</h2>
            <p>Welcome back — here's your quick access panel.</p>
        </aside>
    </section>

    <!-- Specialities -->
    <section class="specialities">
        <div class="heading-row">
            <div class="pill">Specialities</div>
            <div class="special-title">Explore our Centres of Clinical Excellence</div>
        </div>

        <div class="special-grid">
            <a href="speciality-cardiology.html" class="spec-card">
                <img src="icons/heart.svg" alt="cardiology">
                <div>Cardiology</div>
            </a>
            <a href="speciality-ortho.html" class="spec-card">
                <img src="icons/orthopedics.svg" alt="orthopedics">
                <div>Orthopedics</div>
            </a>
            <a href="speciality-derma.html" class="spec-card">
                <img src="icons/dermatology.svg" alt="dermatology">
                <div>Dermatology</div>
            </a>
            <a href="speciality-neuro.html" class="spec-card">
                <img src="icons/neurology.svg" alt="neurology">
                <div>Neurology</div>
            </a>
        </div>
    </section>
</main>

<footer>
    <p>© 2025 SwasthyaTrack. All Rights Reserved.</p>
</footer>
</body>
</html>
