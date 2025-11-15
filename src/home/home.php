<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SwasthyaTrack - Digital Healthcare Management</title>
    <link rel="stylesheet" href="../css/home.css">
    <!-- Adding Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="container">
            <div class="logo">
                <a href="home.php" class="logo-link">
                    <img class="nav-img" src="../images/nav-logo.png" alt="SwasthyaTrack Logo">
                    <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
                </a>
            </div>
            
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" aria-label="Toggle navigation menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <!-- Navigation Links -->
            <nav class="main-nav">
                <a href="#Home" class="nav-link">Home</a>
                <a href="#About" class="nav-link">About</a>
                <a href="#Services" class="nav-link">Services</a>
                <a href="../auth/login.php" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="Home" class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-badge">Digital Healthcare Platform</div>
                    <h1>
                        <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
                    </h1>
                    <p class="hero-description">
                        Your all-in-one digital healthcare management platform. 
                        We modernize patient care by streamlining everything from 
                        appointment booking to managing your medical records.
                    </p>
                    <div class="hero-actions">
                        <a href="../auth/signup.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Register Now
                        </a>
                        <a href="#Services" class="btn btn-secondary">
                            <i class="fas fa-info-circle"></i>
                            Learn More
                        </a>
                    </div>
                </div>
                <div class="hero-img">
                    <img src="../images/Main-Logo.png" alt="Healthcare Management Illustration">
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="About" class="about">
        <div class="container">
            <div class="section-header">
                <h2>About Us</h2>
                <div class="section-divider"></div>
            </div>
            <div class="about-content">
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
            </div>
            <div class="stats-container">
                <div class="stat-item">
                    <div class="stat-value">1000+</div>
                    <div class="stat-label">Patients Served</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">50+</div>
                    <div class="stat-label">Healthcare Partners</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">24/7</div>
                    <div class="stat-label">Support Available</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="Services" class="services">
        <div class="container">
            <div class="section-header">
                <h2>Our Services</h2>
                <div class="section-divider"></div>
                <p class="section-subtitle">Comprehensive healthcare solutions designed for modern needs</p>
            </div>
            <div class="service-container">
                <!-- Service 1 -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Appointment Booking</h3>
                    <p>
                        Easily book, view, and manage appointments with healthcare providers 
                        without the hassle of phone calls or long queues.
                    </p>
                    <div class="service-features">
                        <span><i class="fas fa-check"></i> Real-time availability</span>
                        <span><i class="fas fa-check"></i> Instant confirmations</span>
                        <span><i class="fas fa-check"></i> Reminder notifications</span>
                    </div>
                </div>
                <!-- Service 2 -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-bed"></i>
                    </div>
                    <h3>Bed Booking</h3>
                    <p>
                        Reserve hospital beds in advance with real-time availability tracking 
                        for efficient admission and transfers.
                    </p>
                    <div class="service-features">
                        <span><i class="fas fa-check"></i> Live bed status</span>
                        <span><i class="fas fa-check"></i> Quick reservations</span>
                        <span><i class="fas fa-check"></i> Priority allocation</span>
                    </div>
                </div>
                <!-- Service 3 -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <h3>Medical Reports</h3>
                    <p>
                        Access and view your complete medical reports, test results, 
                        and diagnostic information securely in one place.
                    </p>
                    <div class="service-features">
                        <span><i class="fas fa-check"></i> Secure access</span>
                        <span><i class="fas fa-check"></i> Historical data</span>
                        <span><i class="fas fa-check"></i> Download options</span>
                    </div>
                </div>
                <!-- Service 4 -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>Report Management</h3>
                    <p>
                        Organize, manage, and share your medical reports with healthcare 
                        providers as needed for comprehensive care.
                    </p>
                    <div class="service-features">
                        <span><i class="fas fa-check"></i> Easy organization</span>
                        <span><i class="fas fa-check"></i> Secure sharing</span>
                        <span><i class="fas fa-check"></i> Provider access</span>
                    </div>
                </div>
                <!-- Service 5 -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-prescription"></i>
                    </div>
                    <h3>Prescription Access</h3>
                    <p>
                        View and manage your prescriptions, medication details, and 
                        dosage instructions from your healthcare providers.
                    </p>
                    <div class="service-features">
                        <span><i class="fas fa-check"></i> Digital prescriptions</span>
                        <span><i class="fas fa-check"></i> Medication history</span>
                        <span><i class="fas fa-check"></i> Refill reminders</span>
                    </div>
                </div>
                <!-- Service 6 - New Service -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-comment-medical"></i>
                    </div>
                    <h3>Doctor Consultation</h3>
                    <p>
                        Connect with healthcare professionals for virtual consultations 
                        and get medical advice from the comfort of your home.
                    </p>
                    <div class="service-features">
                        <span><i class="fas fa-check"></i> Virtual appointments</span>
                        <span><i class="fas fa-check"></i> Secure messaging</span>
                        <span><i class="fas fa-check"></i> Video consultations</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Transform Your Healthcare Experience?</h2>
                <p>Join thousands of patients and healthcare providers using SwasthyaTrack</p>
                <div class="cta-actions">
                    <a href="../auth/signup.php" class="btn btn-primary btn-large">
                        <i class="fas fa-rocket"></i>
                        Get Started Today
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="logo">
    <img class="nav-img" src="../images/nav-logo.png" alt="SwasthyaTrack Logo">
    <span class="swasthya-color-footer">Swasthya</span><span class="track-color">Track</span>
</div>

                    <p class="footer-description">
                        Your trusted partner in digital healthcare management.
                    </p>
                </div>
                <div class="footer-links">
                    <div class="footer-column">
                        <h4>Quick Links</h4>
                        <a href="#Home">Home</a>
                        <a href="#About">About</a>
                        <a href="#Services">Services</a>
                    </div>
                    <div class="footer-column">
                        <h4>Account</h4>
                        <a href="../auth/login.php" class="footer-btn">Login</a>
                        <a href="../auth/signup.php" class="footer-btn">Register</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 SwasthyaTrack. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const mainNav = document.querySelector('.main-nav');
            
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    mainNav.classList.toggle('active');
                    mobileMenuToggle.classList.toggle('active');
                });
            }
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                        
                        // Close mobile menu if open
                        if (mainNav.classList.contains('active')) {
                            mainNav.classList.remove('active');
                            mobileMenuToggle.classList.remove('active');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>