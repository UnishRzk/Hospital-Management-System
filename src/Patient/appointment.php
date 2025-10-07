<?php
// Include the database connection file
include("../config/db.php");

// ------------------------------
// Validate doctor_id from URL
// ------------------------------

// Check if doctor_id is missing
if (!isset($_GET['doctor_id'])) {
    http_response_code(400); // Bad Request
    exit("Doctor ID is required.");
}

// Check if doctor_id is not a number
if (!is_numeric($_GET['doctor_id'])) {
    http_response_code(400); // Bad Request
    exit("Invalid doctor ID.");
}

// Convert doctor_id safely to integer
$doctor_id = (int) $_GET['doctor_id'];


// ------------------------------
// Fetch doctor info from database
// ------------------------------
$stmt = $conn->prepare("
    SELECT doctor_id, name, email, department, designation, council_number, phone, photo
    FROM doctors
    WHERE doctor_id = ?
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();
$stmt->close();

// If no doctor found
if (!$doctor) {
    http_response_code(404); // Not Found
    exit("Doctor not found.");
}


// ------------------------------
// Fetch doctor education details
// ------------------------------
$edu_stmt = $conn->prepare("
    SELECT degree, institution, year_of_completion
    FROM doctor_education
    WHERE doctor_id = ?
    ORDER BY year_of_completion
");
$edu_stmt->bind_param("i", $doctor_id);
$edu_stmt->execute();
$education = $edu_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$edu_stmt->close();


// ------------------------------
// Helper function for escaping output (XSS safe)
// ------------------------------
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}


// ------------------------------
// Determine doctor photo path
// ------------------------------
$photoPath = "../images/doctor.png"; // Default photo if none provided

if (!empty($doctor['photo'])) {
    $photoPath = "../images/doctors/" . e($doctor['photo']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dr. <?php echo e($doctor['name']); ?> — Profile</title>
  <style>

/* ===== Appointment Form Styling ===== */
.form-container {
  max-width: 900px;
  margin: 3rem auto;
  background: #fff;
  padding: 2rem 2.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.form-container h2 {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #015eac;
  font-size: 1.8rem;
  font-weight: 800;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.2rem 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-weight: 600;
  margin-bottom: 6px;
  color: #0b1e2d;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 0.6rem 0.8rem;
  border: 1px solid #c5d2df;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color .3s, box-shadow .3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #015eac;
  box-shadow: 0 0 0 3px rgba(1,94,172,0.15);
  outline: none;
}

.error {
  color: #f31026;
  font-size: 0.85rem;
  margin-top: 3px;
  height: 16px;
}

/* Submit Button */
.btn {
  background: #015eac;
  color: #fff;
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background .3s, transform .2s;
  display: block;
  margin: 1.5rem auto 0;
}

.btn:hover {
  background: #f31026;
  transform: translateY(-1px);
}

/* ===== Success Modal Styling ===== */
.modal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.modal.active {
  display: flex;
}

.modal-content {
  background: #fff;
  padding: 2rem 2.5rem;
  border-radius: 10px;
  max-width: 420px;
  text-align: center;
  box-shadow: 0 4px 16px rgba(0,0,0,0.2);
  animation: fadeIn .3s ease;
}

.modal-content h3 {
  color: #015eac;
  margin-bottom: 0.5rem;
}

.modal-content p {
  font-size: 1rem;
  margin-bottom: 1.2rem;
  color: #333;
}

#closeModal {
  background: #015eac;
  color: #fff;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background .3s;
}

#closeModal:hover {
  background: #f31026;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Responsive adjustment */
@media (max-width: 560px) {
  .form-container {
    padding: 1.5rem;
  }
  .form-container h2 {
    font-size: 1.5rem;
  }
}


 body {
      margin: 0;
      font-family: Roboto, Segoe UI, sans-serif;
      background: #cfe1f0;
      color: #0b1e2d;
    }
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 3%;
      background: #ffffffcc;
      backdrop-filter: blur(10px);
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    .logo {
      font-size: 1.5rem;
      font-weight: bold;
      color: #015eac;
    }
    .logo a {
      text-decoration: none;
      color: inherit;
    }
    .nav-img {
      height: 30px;
      width: 40px;
      margin-bottom: 7px;
      vertical-align: middle;
    }
    nav a {
      font-weight: 500;
      color: #000;
      text-decoration: none;
      transition: color .3s, transform .2s;
    }
    nav a:hover {
      color: #f31026;
      transform: translateY(-2px);
    }
    .btn-login {
      padding: .4rem 1rem;
      border: 1px solid #015eac;
      border-radius: 8px;
      color: #015eac;
      transition: .3s;
    }
    .btn-login:hover {
      background: #f31026;
      color: #fff;
      border-color: #fff;
    }
    main {
      min-height: calc(100vh - 100px);
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }
    .profile-wrapper {
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
    }
    .profile-content {
      display: grid;
      grid-template-columns: 380px 0.6fr;
      gap: 48px;
      align-items: start;
      justify-content: center;
    }
    .photo-card {
      max-width: 340px;
    }
    .photo {
      width: 100%;
      height: 360px;
      padding-top: 20px;
      object-fit: cover;
      display: block;
    }
    .name-card {
      background: #fff;
      padding: 18px 16px 22px;
      text-align: center;
      box-shadow: 0 2px 10px rgba(0,0,0,.05);
    }
    .name {
      font-size: 2rem;
      font-weight: 800;
      margin-bottom: 8px;
    }
    .right-col {
      padding: 25px 10px 0;
    }
    .designation-text {
      font-size: 1.25rem;
      font-weight: 700;
      color: #015eac;
    }
    .info-block {
      margin-bottom: 20px;
    }
    .label {
      font-size: 1.4rem;
      font-weight: 700;
      margin-bottom: 6px;
    }
    .value {
      font-size: 1.1rem;
      line-height: 1.3;
    }
    footer {
      background: #015eac;
      color: #fff;
      text-align: center;
      padding: .4rem;
    }
    .swasthya-color { color: #015eac; }
    .track-color { color: #f31026; }
    @media (max-width: 980px) {
      .profile-content {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
        margin: 0 auto
      }
      .left-col{
        justify-self: center;
      }
    }
    @media (max-width: 560px) {
      .photo { height: 300px; }
      .name { font-size: 1.6rem; }
      .designation-text { font-size: 1.05rem; }
    }
  </style>

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
<div class="form-container">
    <h2>Book Appointment</h2>
    <form id="appointmentForm" novalidate>
        <div class="form-grid">
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input id="fullname" type="text" name="fullname" required>
                <span class="error"></span>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input id="phone" type="tel" name="phone" required pattern="^[0-9]{10}$" placeholder="10-digit number">
                <span class="error"></span>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" required>
                <span class="error"></span>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input id="address" type="text" name="address" required>
                <span class="error"></span>
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option>Male</option>
                    <option>Female</option>
                    <option>Other</option>
                </select>
                <span class="error"></span>
            </div>

            <div class="form-group">
                <label for="date">Pick a Date</label>
                <input id="date" type="date" name="date" required>
                <span class="error"></span>
            </div>
        </div>

        <div class="form-group" style="margin-top:1rem;">
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="3" placeholder="Briefly describe your concern..."></textarea>
        </div>

        <button type="submit" class="btn">Confirm Booking</button>
    </form>
</div>

<!-- Modal -->
<div class="modal" id="successModal">
    <div class="modal-content">
        <h3>Appointment Confirmed!</h3>
        <p>Your booking has been successfully recorded. We’ll contact you shortly.</p>
        <button id="closeModal">OK</button>
    </div>
</div>


  <!-- Main Profile Section -->
  <main>
    <div class="profile-wrapper">
      <div class="profile-content">

        <!-- Left Column (Photo + Name) -->
        <section class="left-col">
          <div class="photo-card">
            <img class="photo" src="<?php echo $photoPath; ?>" alt="Doctor photo">
            <div class="name-card">
              <div class="name">Dr. <?php echo e($doctor['name']); ?></div>

              <!-- Show designation if available, otherwise show placeholder -->
              <?php
              if (!empty($doctor['designation'])) {
                  echo '<div class="designation-text">' . e($doctor['designation']) . '</div>';
              } else {
                  echo '<div class="designation-text">Designation</div>';
              }
              ?>
            </div>
          </div>
        </section>

        <!-- Right Column (Details) -->
        <section class="right-col">

          <!-- Department -->
          <div class="info-block">
            <div class="label">Department:</div>
            <div class="value">
              <?php
              if (!empty($doctor['department'])) {
                  echo e($doctor['department']);
              } else {
                  echo "Not specified";
              }
              ?>
            </div>
          </div>

          <!-- Designation -->
          <div class="info-block">
            <div class="label">Designation:</div>
            <div class="value">
              <?php
              if (!empty($doctor['designation'])) {
                  echo e($doctor['designation']);
              } else {
                  echo "Not specified";
              }
              ?>
            </div>
          </div>

          <!-- Education -->
          <div class="info-block">
            <div class="label">Education:</div>
            <div class="value">
              <?php
              if (!empty($education)) {
                  foreach ($education as $edu) {
                      echo e($edu['degree']) . ", " . e($edu['institution']) . " (" . e($edu['year_of_completion']) . ")<br>";
                  }
              } else {
                  echo "No education records.";
              }
              ?>
            </div>
          </div>

          <!-- Council Number -->
          <div class="info-block">
            <div class="label">Council No:</div>
            <div class="value">
              <?php
              if (!empty($doctor['council_number'])) {
                  echo e($doctor['council_number']);
              } else {
                  echo "Not specified";
              }
              ?>
            </div>
          </div>

          <!-- Email -->
          <div class="info-block">
            <div class="label">Email:</div>
            <div class="value">
              <?php
              if (!empty($doctor['email'])) {
                  echo e($doctor['email']);
              } else {
                  echo "Not specified";
              }
              ?>
            </div>
          </div>

          <!-- Phone -->
          <div class="info-block">
            <div class="label">Phone:</div>
            <div class="value">
              <?php
              if (!empty($doctor['phone'])) {
                  echo e($doctor['phone']);
              } else {
                  echo "Not specified";
              }
              ?>
            </div>
          </div>

        </section>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer>
    <p>© 2025 SwasthyaTrack. All Rights Reserved.</p>
  </footer>

<script>
    // Min date restriction
    const dateInput = document.getElementById('date');
    dateInput.min = new Date().toISOString().split("T")[0];

    // Real-time validation
    document.querySelectorAll('input[required], select[required]').forEach(field => {
        field.addEventListener('input', () => {
            const error = field.nextElementSibling;
            if (!field.value.trim()) {
                error.textContent = "This field is required.";
            } else if (field.name === 'phone' && !/^[0-9]{10}$/.test(field.value)) {
                error.textContent = "Enter a valid 10-digit number.";
            } else {
                error.textContent = "";
            }
        });
    });

    // Form submission handler
    document.getElementById('appointmentForm').addEventListener('submit', function(e){
        e.preventDefault();
        let valid = true;

        this.querySelectorAll('input[required], select[required]').forEach(field => {
            const error = field.nextElementSibling;
            if (!field.value.trim()) {
                error.textContent = "This field is required.";
                valid = false;
            } else if (field.name === 'phone' && !/^[0-9]{10}$/.test(field.value)) {
                error.textContent = "Enter a valid 10-digit number.";
                valid = false;
            } else {
                error.textContent = "";
            }
        });

        if (valid) {
            document.getElementById('successModal').classList.add('active');
            this.reset();
        }
    });

    // Close modal
    document.getElementById('closeModal').addEventListener('click', () => {
        document.getElementById('successModal').classList.remove('active');
    });
</script>
</body>
</html>
