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
            <a href="book-appointment.html" class="action-card">
                <div class="icon"><img src="../images/icons/dates.png" alt="calendar"></div>
                <div class="label">Book Appointment</div>
            </a>
            <a href="book-bed.html" class="action-card">
                <div class="icon"><img src="../images/icons/hospital-bed.png" alt="bed"></div>
                <div class="label">Book Bed</div>
            </a>
            <a href="prescriptions.html" class="action-card">
                <div class="icon"><img src="../images/icons/prescription.png" alt="prescriptions"></div>
                <div class="label">View Prescriptions</div>
            </a>
            <a href="reports.html" class="action-card">
                <div class="icon"><img src="../images/icons/report.png" alt="reports"></div>
                <div class="label">View Reports</div>
            </a>
            <a href="upload-report.html" class="action-card">
                <div class="icon"><img src="../images/icons/update-report.png" alt="upload"></div>
                <div class="label">Upload Report</div>
            </a>
            <a href="../pages/find-doctors.php" class="action-card">
                <div class="icon"><img src="../images/icons/doctor.png" alt="doctor"></div>
                <div class="label">Find a Doctor</div>
            </a>
        </div>

        <aside class="greeting">
            <img src="../images/veterinarian.png" alt="Doctor illustration">
            <h2>Hi User!</h2>
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
