<?php
session_start(); // Start the session

// Check if the user is already logged in
if (isset($_SESSION['role'])) {
    // Redirect users to their specific dashboard based on their role
    if ($_SESSION['role'] == 'patient') {
        header("Location: dashboards/patient_dashboard.php");
        exit();
    } else if ($_SESSION['role'] == 'doctor') {
        header("Location: dashboards/doctor_dashboard.php");
        exit();
    } else if ($_SESSION['role'] == 'nurse') {
        header("Location: dashboards/nurse_dashboard.php");
        exit();
    } else if ($_SESSION['role'] == 'admin') {
        header("Location: dashboards/admin_dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SwasthyaTrack</title>
    <link rel="stylesheet" href="../css/home.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="logo">
            <a href="home.php">
                <!-- Website Logo -->
                <img class="nav-img" src="../images/nav-logo.png" alt="SwasthyaTrack Logo">
                <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
            </a>
        </div>
        <!-- Navigation Links -->
        <nav>
            <a href="#Home">Home</a>
            <a href="#About">About</a>
            <a href="#Services">Services</a>
            <a href="../auth/login.php" class="btn-login">Login</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="Home" class="hero">
        <div class="hero-text">
            <h1>
                <span class="swasthya-color">Swasthya</span>
                <span class="track-color">Track</span>
            </h1>
            <p>
                Your all-in-one digital healthcare management platform. 
                We modernize patient care by streamlining everything from 
                appointment booking to managing your medical records.
            </p>
            <a href="../auth/signup.php" class="btn">Register Now</a>
        </div>
        <div class="hero-img">
            <img src="../images/Main-Logo.png" alt="Hero Image">
        </div>
    </section>

    <!-- About Section -->
    <section id="About" class="about">
        <h2>About Us</h2>
        <p>
            SwasthyaTrack is a project born from the need to address the challenges 
            inherent in manual healthcare processes. Our system follows an Agile 
            development methodology, ensuring a flexible and high-quality product.
        </p>
        <p>
            We aim to digitize records, reduce paperwork, and improve operational 
            efficiency. Our mission is to empower healthcare professionals with 
            real-time data and provide patients with secure access to their 
            health information.
        </p>
    </section>

    <!-- Services Section -->
    <section id="Services" class="services">
        <h2>Our Services</h2>
        <div class="service-container">
            <!-- Service 1 -->
            <div class="service-card">
                <img src="../images/reminder-appointment.png" alt="Appointment">
                <h3>Online Appointment Booking</h3>
                <p>
                    Easily book, view, and manage appointments with your doctors 
                    without the hassle of phone calls or long queues.
                </p>
            </div>
            <!-- Service 2 -->
            <div class="service-card">
                <img src="../images/notebook-alt.png" alt="Medical Records">
                <h3>Electronic Medical Records</h3>
                <p>
                    Access a complete, secure, and centralized digital medical record 
                    including your visit history, diagnoses, and lab results.
                </p>
            </div>
            <!-- Service 3 -->
            <div class="service-card">
                <img src="../images/bed.png" alt="Bed Management">
                <h3>Bed Management</h3>
                <p>
                    Real-time tracking of bed availability ensures efficient patient 
                    admission and transfers for hospital staff.
                </p>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer>
        <p>© 2025 SwasthyaTrack. All Rights Reserved.</p>
    </footer>
</body>
</html>
