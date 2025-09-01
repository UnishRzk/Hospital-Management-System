<?php
session_start();
include("../config/db.php");

if ($_SESSION['role'] != 'admin') {
    die("Access denied");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);

    $department      = !empty($_POST['department']) ? trim($_POST['department']) : null;
    $designation     = !empty($_POST['designation']) ? trim($_POST['designation']) : null;
    $council_number  = !empty($_POST['council_number']) ? trim($_POST['council_number']) : null;

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        if ($role == 'doctor') {
            // Handle doctor photo upload
            $photoFilename = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = "../images/doctors/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $photoFilename = "doctor_" . time() . "_" . uniqid() . "." . $ext;
                $targetPath = $uploadDir . $photoFilename;
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                    $error = "Failed to upload photo.";
                }
            }

            $stmt2 = $conn->prepare("INSERT INTO doctors 
                (user_id, name, email, department, designation, council_number, phone, photo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("isssssss", $user_id, $name, $email, $department, $designation, $council_number, $phone, $photoFilename);

            if ($stmt2->execute()) {
                $doctor_id = $stmt2->insert_id;

                // Insert multiple education entries
                if (!empty($_POST['degree']) && is_array($_POST['degree'])) {
                    $degrees      = $_POST['degree'];
                    $institutions = $_POST['institution'];
                    $years        = $_POST['year_of_completion'];

                    for ($i = 0; $i < count($degrees); $i++) {
                        $deg  = trim($degrees[$i]);
                        $inst = trim($institutions[$i]);
                        $yr   = trim($years[$i]);

                        if ($deg && $inst && $yr) {
                            $stmt3 = $conn->prepare("INSERT INTO doctor_education 
                                (doctor_id, degree, institution, year_of_completion) 
                                VALUES (?, ?, ?, ?)");
                            $stmt3->bind_param("isss", $doctor_id, $deg, $inst, $yr);
                            $stmt3->execute();
                        }
                    }
                }
            }

        } elseif ($role == 'nurse') {
            $stmt2 = $conn->prepare("INSERT INTO nurses (user_id, name, email, phone) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("isss", $user_id, $name, $email, $phone);
            $stmt2->execute();

        } elseif ($role == 'admin') {
            $stmt2 = $conn->prepare("INSERT INTO admins (user_id, name, email) VALUES (?, ?, ?)");
            $stmt2->bind_param("iss", $user_id, $name, $email);
            $stmt2->execute();
        }

        $success = "User created successfully.";
    } else {
        $error = "Error creating user. Username may already exist.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create User | SwasthyaTrack</title>
    <style>
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Roboto','Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body { line-height: 1.6; background: #f9f9fb; color: #000; }

        header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1rem 3%; background: #ffffffcc;
            backdrop-filter: blur(10px); box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky; top: 0; z-index: 1000;
        }
        .logo { font-size: 1.5rem; font-weight: bold; color: #015eac; display: flex; align-items: center; gap: 0.4rem; }
        .swasthya-color { color: #015eac; }
        .track-color { color: #f31026; }
        .nav-img { height: 30px; width: 40px; margin-bottom: 5px; }

        .form-section {
            display: flex; justify-content: center; align-items: center;
            min-height: 81vh; padding: 2rem;
            background: linear-gradient(120deg, #e8f0ff, #f9f9fb);
        }
        .form-card {
            background: #fff; padding: 2.5rem; border-radius: 16px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            max-width: 500px; width: 100%;
        }
        .form-card h2 { margin-bottom: 1.5rem; font-size: 26px; color: #015eac; text-align: center; }
        .form-card form { display: flex; flex-direction: column; gap: 1rem; }
        .form-card input, .form-card select {
            padding: 0.8rem 1rem; border: 1px solid #ccc; border-radius: 8px; font-size: 1rem;
        }
        .form-card input:focus, .form-card select:focus { border-color: #015eac; outline: none; }

        .doctor-fields { display: none; flex-direction: column; gap: 1rem; margin-top: 0.5rem; }
        .education-entry { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem; }
        .education-entry input { width: 100%; }

        .add-btn {
            background: #f0f0f0; border: 1px dashed #999; padding: 0.6rem;
            border-radius: 6px; cursor: pointer; font-size: 0.9rem;
        }
        .add-btn:hover { background: #e0e0e0; }

        .create-btn {
            background: #015eac; color: #fff; border: none;
            padding: 0.9rem; border-radius: 8px; font-size: 1rem; cursor: pointer;
        }
        .create-btn:hover { background: #1f4fbf; }

        .message { text-align: center; margin-bottom: 1rem; font-size: 0.95rem; }
        .success { color: green; } .error { color: red; }

        footer { background: #015eac; color: #fff; text-align: center; padding: 1rem; }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <a href="../index.php">
            <img class="nav-img" src="../images/nav-logo.png" alt="">
            <span class="swasthya-color">Swasthya</span><span class="track-color">Track</span>
        </a>
    </div>
</header>

<section class="form-section">
    <div class="form-card">
        <h2>Create User (Admin)</h2>

        <?php if (!empty($success)) echo "<p class='message success'>$success</p>"; ?>
        <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Phone">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>

            <select name="role" id="roleSelect" required onchange="toggleDoctorFields()">
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="doctor">Doctor</option>
                <option value="nurse">Nurse</option>
            </select>

            <!-- Doctor-specific fields -->
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
</section>

<footer>
    <p>&copy; 2025 SwasthyaTrack. All Rights Reserved.</p>
</footer>

<script>
function toggleDoctorFields() {
    const role = document.getElementById("roleSelect").value;
    document.getElementById("doctorFields").style.display = (role === "doctor") ? "flex" : "none";
}
function addEducation() {
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
