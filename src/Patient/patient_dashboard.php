<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

session_start();

require_once '../config/db.php';

// Enforce login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = "User"; // Default fallback

// Fetch first name
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

    <style>
        /* ===== Logout Modal Styling ===== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal-overlay.active {
            visibility: visible;
            opacity: 1;
        }

        .logout-modal {
            background: #fff;
            border-radius: 20px;
            padding: 32px 28px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: popIn 0.3s ease;
        }

        @keyframes popIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .logout-modal h3 {
            font-size: 1.4rem;
            color: #0b2236;
            margin-bottom: 12px;
        }

        .logout-modal p {
            color: #64748b;
            margin-bottom: 28px;
        }

        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 16px;
        }

        .modal-btn {
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel {
            background-color: #e2e8f0;
            color: #0b2236;
        }

        .btn-cancel:hover {
            background-color: #cbd5e1;
        }

        .btn-confirm {
            background-color: #f31026;
            color: #fff;
        }

        .btn-confirm:hover {
            background-color: #d90c20;
        }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <a href="patient_dashboard.php">
            <img class="nav-img" src="../images/nav-logo.png" alt="">
            <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
        </a>
    </div>
    <nav>
        <a href="patient_dashboard.php">Home</a>
        <a href="my_appointments.php">Appointments</a>
        <a href="my_prescriptions.php">Prescriptions</a>
        <a href="my_reports.php">Reports</a>
        <a href="../auth/logout.php" class="btn-login" id="logout-link">Logout</a>
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
                <div class="label">Bed Reservations</div>
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

<!-- Logout Modal -->
<div class="modal-overlay" id="logout-modal">
    <div class="logout-modal">
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to logout from your account?</p>
        <div class="modal-actions">
            <button class="modal-btn btn-cancel" id="cancel-logout">Cancel</button>
            <button class="modal-btn btn-confirm" id="confirm-logout">Logout</button>
        </div>
    </div>
</div>

<script>
const logoutLink = document.getElementById('logout-link');
const logoutModal = document.getElementById('logout-modal');
const cancelBtn = document.getElementById('cancel-logout');
const confirmBtn = document.getElementById('confirm-logout');

logoutLink.addEventListener('click', (e) => {
    e.preventDefault();
    logoutModal.classList.add('active');
});

cancelBtn.addEventListener('click', () => {
    logoutModal.classList.remove('active');
});

confirmBtn.addEventListener('click', () => {
    window.location.href = logoutLink.href;
});
</script>
</body>
</html>
