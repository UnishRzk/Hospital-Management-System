<?php
// Start the session to access session variables
session_start();

// Security Check 
// Redirect to login page if the user is not logged in or is not a patient.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit(); 
}

// include("../config/db.php");

// Helper function for XSS protection 
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Handle Form Submission (Backend Logic) 
// This block will process the bed selection when a form is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bed_type = $_POST['bed_type'] ?? '';

    // --- Validation ---
    $allowed_bed_types = ['private', 'semi-private', 'general'];
    if (in_array($bed_type, $allowed_bed_types)) {
        header("Location: patient_dashboard.php?booking_status=success&type=" . e($bed_type));
        exit();
    } else {
        // Handle invalid bed type submission
        $error_message = "Invalid bed type selected. Please try again.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>Book a Bed — SwasthyaTrack</title>
    <link rel="stylesheet" href="../css/patient-dashboard.css">
    <style>
        /* --- Main Container for the Booking Page --- */
.bed-booking-main {
    padding: 2rem 1rem; 
    background-color: #f4f7f9; 
    min-height: calc(100vh - 120px); 
}

.booking-container {
    max-width: 1200px;
    margin: 0 auto; /* Center the container */
    padding: 2rem;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
}

.booking-container h2 {
    text-align: center;
    font-size: 2rem;
    font-weight: 700;
    color: #015eac; 
    margin-bottom: 2.5rem;
}

/* --- Wrapper for the Bed Option Cards --- */
.bed-options-wrapper {
    display: flex;
    justify-content: center; 
    gap: 2rem; 
    flex-wrap: wrap; 
}

/* --- Styling for Each Bed Card --- */
.bed-card {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden; 
    flex: 1 1 300px; 
    max-width: 340px;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.bed-card:hover {
    transform: translateY(-8px); 
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12); 
}

.bed-card img {
    width: 100%;
    height: 200px; 
    object-fit: cover; 
    display: block;
}

.card-content {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1; 
}

.card-content h3 {
    font-size: 1.5rem;
    color: #0b1e2d;
    margin-bottom: 1rem;
    text-align: center;
}

.card-content ul {
    list-style: none;
    padding: 0;
    margin: 0 0 1.5rem 0;
    flex-grow: 1; 
}

.card-content li {
    color: #333;
    margin-bottom: 0.75rem;
    font-size: 1rem;
    display: flex;
    align-items: center;
}

.tick {
    color: #28a745; 
    font-weight: bold;
    margin-right: 10px;
}


.btn-select {
    display: block;
    width: 100%;
    padding: 0.75rem 1rem;
    background-color: #015eac; 
    color: #fff;
    font-size: 1rem;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    transition: background-color 0.3s ease, transform 0.2s ease;
    margin-top: auto; 
}

.btn-select:hover {
    background-color: #f31026; 
    transform: translateY(-2px);
}

.error-notice {
    text-align: center;
    background-color: #f8d7da;
    color: #721c24;
    padding: 1rem;
    border: 1px solid #f5c6cb;
    border-radius: 8px;
    margin: 0 auto 2rem auto;
    max-width: 600px;
}

@media (max-width: 768px) {
    .booking-container h2 {
        font-size: 1.8rem;
        margin-bottom: 2rem;
    }

    .bed-card {
        flex-basis: 80%; 
    }
}

@media (max-width: 480px) {
    .bed-booking-main, .booking-container {
        padding: 1rem;
    }

    .booking-container h2 {
        font-size: 1.5rem;
    }

    .bed-card {
        flex-basis: 95%; 
    }
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
        <a href="book_appointment.php">Book Appointment</a>
        <a href="my_appointments.php">Appointments</a>
        <!-- <a href="bed_type.php">Book Bed</a> -->
        <!-- <a href="#About">Bed Reservations</a> -->
        <!-- <a href="my_prescriptions.php">Prescriptions</a> -->
        <a href="my_reports.php">Reports</a>
        <a href="patient_dashboard.php" class="btn-login">Back</a>
    </nav>
</header>

<main role="main" class="bed-booking-main">
    <div class="booking-container">
        <h2>View and Select Bed Type</h2>
        
        <?php if (isset($error_message)): ?>
            <p class="error-notice"><?php echo e($error_message); ?></p>
        <?php endif; ?>

        <div class="bed-options-wrapper">

            <div class="bed-card">
                <img src="../images/rooms/private_room.png" alt="Private Room">
                <div class="card-content">
                    <h3>Private Room</h3>
                    <ul>
                        <li><span class="tick">✔</span> WiFi</li>
                        <li><span class="tick">✔</span> AC</li>
                        <li><span class="tick">✔</span> Attached Bathroom</li>
                        <li><span class="tick">✔</span> Personal TV</li>
                    </ul>
                    <form method="POST" action="book_bed_private.php">
                        <input type="hidden" name="bed_type" value="private">
                        <button type="submit" class="btn-select">Select this Room</button>
                    </form>
                </div>
            </div>

            <div class="bed-card">
                <img src="../images/rooms/semi_private_room.png" alt="Semi-Private Room">
                <div class="card-content">
                    <h3>Semi-Private Room</h3>
                    <ul>
                        <li><span class="tick">✔</span> WiFi</li>
                        <li><span class="tick">✔</span> AC</li>
                        <li><span class="tick">✔</span> Shared Bathroom</li>
                    </ul>
                    <form method="POST" action="book_bed_semiprivate.php">
                        <input type="hidden" name="bed_type" value="semi-private">
                        <button type="submit" class="btn-select">Select this Room</button>
                    </form>
                </div>
            </div>

            <div class="bed-card">
                <img src="../images/rooms/general_ward.png" alt="General Ward Bed">
                <div class="card-content">
                    <h3>General Ward Bed</h3>
                    <ul>
                        <li><span class="tick">✔</span> WiFi</li>
                        <li><span class="tick">✔</span> Shared Bathroom</li>
                    </ul>
                    <form method="POST" action="book_bed_general.php">
                        <input type="hidden" name="bed_type" value="general">
                        <button type="submit" class="btn-select">Select this Room</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<footer>
    <p>© <?php echo date("Y"); ?> SwasthyaTrack. All Rights Reserved.</p>
</footer>

</body>
</html>