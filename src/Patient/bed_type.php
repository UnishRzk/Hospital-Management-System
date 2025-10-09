<?php
// Start the session to access session variables
session_start();

// --- Security Check ---
// Redirect to login page if the user is not logged in or is not a patient.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit(); // Stop script execution
}

// You can include your database connection here if needed for dynamic data
// include("../config/db.php");

// --- Helper function for XSS protection ---
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// --- Handle Form Submission (Backend Logic) ---
// This block will process the bed selection when a form is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bed_type = $_POST['bed_type'] ?? '';

    // --- Validation ---
    $allowed_bed_types = ['private', 'semi-private', 'general'];
    if (in_array($bed_type, $allowed_bed_types)) {
        // --- TODO: Backend Processing ---
        // 1. Check bed availability in the database.
        // 2. Create a reservation record associated with the patient's ID ($_SESSION['user_id']).
        // 3. Redirect to a confirmation/success page or display a message.

        // For now, we'll just redirect to the dashboard with a success message.
        // Replace this with your actual booking logic.
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
    padding: 2rem 1rem; /* Add some padding for spacing */
    background-color: #f4f7f9; /* A light background color for the page */
    min-height: calc(100vh - 120px); /* Ensure it takes up most of the screen height */
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
    color: #015eac; /* Using your primary blue color */
    margin-bottom: 2.5rem;
}

/* --- Wrapper for the Bed Option Cards --- */
.bed-options-wrapper {
    display: flex;
    justify-content: center; /* Center cards horizontally */
    gap: 2rem; /* Space between cards */
    flex-wrap: wrap; /* Allow cards to wrap to the next line on smaller screens */
}

/* --- Styling for Each Bed Card --- */
.bed-card {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden; /* Ensures the image corners are rounded */
    flex: 1 1 300px; /* Flex properties for responsive sizing */
    max-width: 340px;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.bed-card:hover {
    transform: translateY(-8px); /* Lifts the card on hover */
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12); /* Adds a more prominent shadow */
}

.bed-card img {
    width: 100%;
    height: 200px; /* Fixed height for all images */
    object-fit: cover; /* Ensures images cover the area without distortion */
    display: block;
}

.card-content {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1; /* Allows this section to grow, pushing the button to the bottom */
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
    flex-grow: 1; /* Pushes button down by taking available space */
}

.card-content li {
    color: #333;
    margin-bottom: 0.75rem;
    font-size: 1rem;
    display: flex;
    align-items: center;
}

.tick {
    color: #28a745; /* Green color for the checkmark */
    font-weight: bold;
    margin-right: 10px;
}

/* --- Select Button Styling --- */
.btn-select {
    display: block;
    width: 100%;
    padding: 0.75rem 1rem;
    background-color: #015eac; /* Primary blue */
    color: #fff;
    font-size: 1rem;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    transition: background-color 0.3s ease, transform 0.2s ease;
    margin-top: auto; /* Pushes the button to the bottom of the card */
}

.btn-select:hover {
    background-color: #f31026; /* Your brand's red color for hover */
    transform: translateY(-2px);
}

/* --- Error Message Styling --- */
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

/* --- Responsive Design --- */
@media (max-width: 768px) {
    .booking-container h2 {
        font-size: 1.8rem;
        margin-bottom: 2rem;
    }

    .bed-card {
        flex-basis: 80%; /* Cards take up more width on smaller screens */
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
        flex-basis: 95%; /* Almost full width on mobile */
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
        <a href="../auth/logout.php" class="btn-login">Logout</a>
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