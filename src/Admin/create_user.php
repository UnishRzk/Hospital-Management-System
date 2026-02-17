<?php
// Start the session to check user role
session_start();

// Connect to the database
include("../config/db.php");

// Only allow admins to access this page
// if ($_SESSION['role'] != 'admin') {
//     die("Access denied. Only admins can create new users.");
// }

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Handle form submission when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and clean form inputs
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);

    // Optional doctor-specific fields
    $department = null;
    if (!empty($_POST['department'])) {
        $department = trim($_POST['department']);
    }

    $designation = null;
    if (!empty($_POST['designation'])) {
        $designation = trim($_POST['designation']);
    }

    $council_number = null;
    if (!empty($_POST['council_number'])) {
        $council_number = trim($_POST['council_number']);
    }

    // Start a database transaction (so all queries succeed or none do)
    $conn->begin_transaction();

    try {
        // Insert into the main users table
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);

        if (!$stmt->execute()) {
            throw new Exception("Could not create user. Username may already exist.");
        }

        // Get the inserted user ID
        $user_id = $stmt->insert_id;
        $stmt->close();

        // If the new user is a doctor
        if ($role == 'doctor') {
            $photoFilename = null;

            // Check if a photo was uploaded
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = "../images/doctors/";

                // Create the directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Generate a unique file name for the photo
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $photoFilename = "doctor_" . time() . "_" . uniqid() . "." . $ext;

                $targetPath = $uploadDir . $photoFilename;

                // Move the uploaded file to the target directory
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                    throw new Exception("Photo upload failed. User not created.");
                }
            }

            // Insert doctor details
            $stmt2 = $conn->prepare("INSERT INTO doctors 
                (user_id, name, email, department, designation, council_number, phone, photo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("isssssss", $user_id, $name, $email, $department, $designation, $council_number, $phone, $photoFilename);

            if (!$stmt2->execute()) {
                throw new Exception("Could not create doctor record.");
            }

            $doctor_id = $stmt2->insert_id;
            $stmt2->close();

            // Insert education records if provided
            if (!empty($_POST['degree']) && is_array($_POST['degree'])) {
                for ($i = 0; $i < count($_POST['degree']); $i++) {
                    $deg = trim($_POST['degree'][$i]);
                    $inst = trim($_POST['institution'][$i]);
                    $yr = trim($_POST['year_of_completion'][$i]);

                    // Only insert if all fields are filled
                    if ($deg != "" && $inst != "" && $yr != "") {
                        $stmt3 = $conn->prepare("INSERT INTO doctor_education 
                            (doctor_id, degree, institution, year_of_completion) 
                            VALUES (?, ?, ?, ?)");
                        $stmt3->bind_param("isss", $doctor_id, $deg, $inst, $yr);

                        if (!$stmt3->execute()) {
                            throw new Exception("Could not insert education record.");
                        }

                        $stmt3->close();
                    }
                }
            }
        }

        // If the new user is a nurse
        else if ($role == 'nurse') {
            $stmt2 = $conn->prepare("INSERT INTO nurses (user_id, name, email, phone) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("isss", $user_id, $name, $email, $phone);

            if (!$stmt2->execute()) {
                throw new Exception("Could not create nurse record.");
            }

            $stmt2->close();
        }

        // If the new user is an admin
        else if ($role == 'admin') {
            $stmt2 = $conn->prepare("INSERT INTO admins (user_id, name, email) VALUES (?, ?, ?)");
            $stmt2->bind_param("iss", $user_id, $name, $email);

            if (!$stmt2->execute()) {
                throw new Exception("Could not create admin record.");
            }

            $stmt2->close();
        }

        // If everything worked, commit the transaction
        $conn->commit();
        $success = "User created successfully.";
    } catch (Exception $e) {
        // If there was an error, rollback the transaction
        $conn->rollback();
        $error = $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Users | Admin Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    /* Basic Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Roboto','Segoe UI',sans-serif;
    }

    body {
      display: flex;
      height: 100vh;
      background: #f9f9fb;
      color: #000;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background: #015eac;
      color: #fff;
      padding: 20px 0;
      display: flex;
      flex-direction: column;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 30px;
      font-size: 1.5rem;
      font-weight: bold;
    }

    .sidebar a {
      display: block;
      padding: 12px 20px;
      color: #fff;
      text-decoration: none;
      font-weight: 500;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background: #004d91;
      border-left: 4px solid #fff;
    }

    /* Main content */
    .main {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
    }

    .topbar {
      background: #fff;
      padding: 15px 20px;
      border-radius: 12px;
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .topbar h1 {
      color: #015eac;
      font-size: 1.6rem;
    }

    .menu-toggle {
      display: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #015eac;
    }

    /* Form Card */
    .form-card {
      background: #fff;
      padding: 2rem;
      border-radius: 16px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.08);
      max-width: 600px;
      margin: auto;
    }

    .form-card h2 {
      margin-bottom: 1rem;
      font-size: 24px;
      color: #015eac;
      text-align: center;
    }

    .form-card form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .form-card input,
    .form-card select {
      padding: 0.8rem 1rem;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    .form-card input:focus,
    .form-card select:focus {
      border-color: #015eac;
      outline: none;
    }

    .doctor-fields {
      display: none;
      flex-direction: column;
      gap: 1rem;
    }

    .education-entry {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }

    .add-btn {
      background: #f0f0f0;
      border: 1px dashed #999;
      padding: 0.6rem;
      border-radius: 6px;
      cursor: pointer;
    }

    .add-btn:hover {
      background: #e0e0e0;
    }

    .create-btn {
      background: #015eac;
      color: #fff;
      border: none;
      padding: 0.9rem;
      border-radius: 8px;
      cursor: pointer;
    }

    .create-btn:hover {
      background: #1f4fbf;
    }

    .message {
      text-align: center;
      font-size: 0.95rem;
    }

    .success {
      color: green;
    }

    .error {
      color: red;
    }

    /* Mobile responsive */
    @media(max-width:992px) {
      body {
        flex-direction: column;
      }

      .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        transform: translateX(-100%);
        z-index: 1000;
        transition: transform 0.3s;
      }

      .sidebar.show {
        transform: translateX(0);
      }

      .menu-toggle {
        display: block;
        margin-right: 15px;
      }

      .topbar {
        justify-content: flex-start;
        gap: 15px;
      }
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php" >Dashboard</a>
    <a href="create_user.php" class="active">Add Users</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_appointments.php">Manage Appointments</a>
    <a href="manage_beds.php">Manage Beds</a>
    <a href="../auth/logout.php">Logout</a>
  </div>

  <!-- Main Content -->
  <div class="main">
    <div class="topbar">
      <span class="menu-toggle" onclick="toggleSidebar()">☰</span>
      <h1>Add Users</h1>
    </div>

    <div class="form-card">
      <h2>Create User (Admin)</h2>

      <!-- Display success message if available -->
      <?php
      if (!empty($success)) {
        echo "<p class='message success'>$success</p>";
      }
      ?>

      <!-- Display error message if available -->
      <?php
      if (!empty($error)) {
        echo "<p class='message error'>$error</p>";
      }
      ?>

      <!-- User creation form -->
      <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>

        <!-- Role selection -->
        <select name="role" id="roleSelect" required onchange="toggleDoctorFields()">
          <option value="">Select Role</option>
          <option value="admin">Admin</option>
          <option value="doctor">Doctor</option>
          <option value="nurse">Nurse</option>
        </select>

        <!-- Extra fields for doctors -->
        <div id="doctorFields" class="doctor-fields">
          <input type="text" name="department" placeholder="Department">
          <input type="text" name="designation" placeholder="Designation">
          <input type="text" name="council_number" placeholder="Council Number">

          <label>Profile Photo:</label>
          <input type="file" name="photo" accept="image/*">

          <h4>Educational Qualification</h4>
          <div id="educationWrapper">
            <div class="education-entry">
              <input type="text" name="degree[]" placeholder="Degree (e.g., MBBS, MD)">
              <input type="text" name="institution[]" placeholder="Institution (e.g., Harvard)">
              <input type="number" name="year_of_completion[]" placeholder="Year (YYYY)" min="1900" max="2099">
            </div>
          </div>
          <button type="button" class="add-btn" onclick="addEducation()">+ Add Education</button>
        </div>

        <button type="submit" class="create-btn">Create User</button>
      </form>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    // Show/Hide sidebar for mobile
    function toggleSidebar() {
      var sidebar = document.getElementById("sidebar");
      sidebar.classList.toggle("show");
    }

    // Show extra fields if role = doctor
    function toggleDoctorFields() {
      var role = document.getElementById("roleSelect").value;
      var doctorFields = document.getElementById("doctorFields");

      if (role === "doctor") {
        doctorFields.style.display = "flex";
      } else {
        doctorFields.style.display = "none";
      }
    }

    // Add a new set of education fields
    function addEducation() {
      var wrapper = document.getElementById("educationWrapper");

      var div = document.createElement("div");
      div.classList.add("education-entry");

      div.innerHTML = `
        <input type="text" name="degree[]" placeholder="Degree (e.g., MBBS, MD)">
        <input type="text" name="institution[]" placeholder="Institution (e.g., Harvard)">
        <input type="number" name="year_of_completion[]" placeholder="Year (YYYY)" min="1900" max="2099">
      `;

      wrapper.appendChild(div);
    }
  </script>
</body>
</html>

