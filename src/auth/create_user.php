<?php
// Start the session so we can check the logged-in user's role
session_start();

// Connect to the database
include("../config/db.php");

// Make sure only admins can access this page
if ($_SESSION['role'] != 'admin') {
    die("Access denied. Only admins can create new users.");
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form input values
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password for security
    $role     = $_POST['role'];
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);

    // These fields are only for doctors
    $department = null;
    $designation = null;
    $council_number = null;

    if (!empty($_POST['department'])) {
        $department = trim($_POST['department']);
    }
    if (!empty($_POST['designation'])) {
        $designation = trim($_POST['designation']);
    }
    if (!empty($_POST['council_number'])) {
        $council_number = trim($_POST['council_number']);
    }

    // Start a database transaction (all queries must succeed or everything is undone)
    $conn->begin_transaction();

    try {
        // Insert into the "users" table
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);

        if (!$stmt->execute()) {
            throw new Exception("Could not create user. The username might already exist.");
        }

        // Get the ID of the newly created user
        $user_id = $stmt->insert_id;
        $stmt->close();

        // Now insert into the role-specific tables
        if ($role == 'doctor') {
            // Handle photo upload
            $photoFilename = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = "../images/doctors/";

                // If the directory does not exist, create it
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Generate a unique filename
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $photoFilename = "doctor_" . time() . "_" . uniqid() . "." . $ext;
                $targetPath = $uploadDir . $photoFilename;

                // Move the uploaded file to the target folder
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                    throw new Exception("Photo upload failed. User not created.");
                }
            }

            // Insert into the "doctors" table
            $stmt2 = $conn->prepare("INSERT INTO doctors 
                (user_id, name, email, department, designation, council_number, phone, photo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("isssssss", $user_id, $name, $email, $department, $designation, $council_number, $phone, $photoFilename);

            if (!$stmt2->execute()) {
                throw new Exception("Could not create doctor record.");
            }

            $doctor_id = $stmt2->insert_id;
            $stmt2->close();

            // Insert doctor education records if provided
            if (!empty($_POST['degree']) && is_array($_POST['degree'])) {
                $degrees = $_POST['degree'];
                $institutions = $_POST['institution'];
                $years = $_POST['year_of_completion'];

                for ($i = 0; $i < count($degrees); $i++) {
                    $deg = trim($degrees[$i]);
                    $inst = trim($institutions[$i]);
                    $yr = trim($years[$i]);

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

        } else if ($role == 'nurse') {
            // Insert into "nurses" table
            $stmt2 = $conn->prepare("INSERT INTO nurses (user_id, name, email, phone) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("isss", $user_id, $name, $email, $phone);

            if (!$stmt2->execute()) {
                throw new Exception("Could not create nurse record.");
            }
            $stmt2->close();

        } else if ($role == 'admin') {
            // Insert into "admins" table
            $stmt2 = $conn->prepare("INSERT INTO admins (user_id, name, email) VALUES (?, ?, ?)");
            $stmt2->bind_param("iss", $user_id, $name, $email);

            if (!$stmt2->execute()) {
                throw new Exception("Could not create admin record.");
            }
            $stmt2->close();
        }

        // If everything succeeded, save changes
        $conn->commit();
        $success = "User created successfully.";

    } catch (Exception $e) {
        // If there was any error, undo everything
        $conn->rollback();
        $error = $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create User | SwasthyaTrack</title>
    <style>
        /* === General Reset and Font === */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto','Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            line-height: 1.6;
            background-color: #f9f9fb;
            color: #000;
        }

        /* === Header Styles === */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 3%;
            background-color: #ffffffcc; /* semi-transparent white */
            backdrop-filter: blur(10px); /* adds blur effect */
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #015eac;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .swasthya-color {
            color: #015eac;
        }

        .track-color {
            color: #f31026;
        }

        .nav-img {
            height: 30px;
            width: 40px;
            margin-bottom: 5px;
        }

        /* === Form Section Background === */
        .form-section {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 81vh;
            padding: 2rem;
            background: linear-gradient(120deg, #e8f0ff, #f9f9fb);
        }

        /* === Form Card === */
        .form-card {
            background-color: #fff;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            max-width: 500px;
            width: 100%;
        }

        .form-card h2 {
            margin-bottom: 1.5rem;
            font-size: 26px;
            color: #015eac;
            text-align: center;
        }

        /* === Input Fields and Select Box === */
        .form-card form {
            display: flex;
            flex-direction: column;
            gap: 1rem; /* space between inputs */
        }

        .form-card input,
        .form-card select {
            padding: 0.8rem 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-card input:focus,
        .form-card select:focus {
            border-color: #015eac;
            outline: none;
        }

        /* === Doctor Specific Fields === */
        .doctor-fields {
            display: none; /* hidden by default */
            flex-direction: column;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        /* === Education Entries for Doctors === */
        .education-entry {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .education-entry input {
            width: 100%;
        }

        /* === Add Education Button === */
        .add-btn {
            background-color: #f0f0f0;
            border: 1px dashed #999;
            padding: 0.6rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .add-btn:hover {
            background-color: #e0e0e0;
        }

        /* === Submit Button === */
        .create-btn {
            background-color: #015eac;
            color: #fff;
            border: none;
            padding: 0.9rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
        }

        .create-btn:hover {
            background-color: #1f4fbf;
        }

        /* === Messages (success / error) === */
        .message {
            text-align: center;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        /* === Footer === */
        footer {
            background-color: #015eac;
            color: #fff;
            text-align: center;
            padding: 1rem;
        }
    </style>
</head>
<body>

<!-- === Page Header === -->
<header>
    <div class="logo">
        <a href="../index.php">
            <img class="nav-img" src="../images/nav-logo.png" alt="Logo">
            <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
        </a>
    </div>
</header>

<!-- === Main Form Section === -->
<section class="form-section">
    <div class="form-card">
        <h2>Create User (Admin)</h2>

        <!-- Show success or error messages -->
        <?php if (!empty($success)) { echo "<p class='message success'>$success</p>"; } ?>
        <?php if (!empty($error)) { echo "<p class='message error'>$error</p>"; } ?>

        <!-- User Creation Form -->
        <form method="POST" enctype="multipart/form-data">
            <!-- Common fields -->
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

            <!-- === Doctor Fields (shown only if Doctor is selected) === -->
            <div id="doctorFields" class="doctor-fields">
                <input type="text" name="department" placeholder="Department">
                <input type="text" name="designation" placeholder="Designation">
                <input type="text" name="council_number" placeholder="Council Number">

                <label>Profile Photo:</label>
                <input type="file" name="photo" accept="image/*">

                <!-- Doctor Education Section -->
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

            <!-- Submit Button -->
            <button type="submit" class="create-btn">Create User</button>
        </form>
    </div>
</section>

<!-- === Footer === -->
<footer>
    <p>&copy; 2025 SwasthyaTrack. All Rights Reserved.</p>
</footer>

<!-- === JavaScript Functions === -->
<script>
function toggleDoctorFields() {
    // Show doctor-specific fields only when "Doctor" is selected
    const role = document.getElementById("roleSelect").value;
    const doctorFields = document.getElementById("doctorFields");

    if (role === "doctor") {
        doctorFields.style.display = "flex";
    } else {
        doctorFields.style.display = "none";
    }
}

function addEducation() {
    // Add a new set of education input fields
    const wrapper = document.getElementById("educationWrapper");

    const div = document.createElement("div");
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

