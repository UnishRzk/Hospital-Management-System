<?php
// Start the session so we can check the logged-in user's role
session_start();

// Connect to the database
include("../config/db.php");

// Make sure only admins can access this page
if ($_SESSION['role'] != 'admin') {
    die("Access denied. Only admins can create new users.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);

    $department = !empty($_POST['department']) ? trim($_POST['department']) : null;
    $designation = !empty($_POST['designation']) ? trim($_POST['designation']) : null;
    $council_number = !empty($_POST['council_number']) ? trim($_POST['council_number']) : null;

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);
        if (!$stmt->execute()) throw new Exception("Could not create user. Username may already exist.");
        $user_id = $stmt->insert_id;
        $stmt->close();

        if ($role == 'doctor') {
            $photoFilename = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = "../images/doctors/";
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $photoFilename = "doctor_" . time() . "_" . uniqid() . "." . $ext;
                $targetPath = $uploadDir . $photoFilename;
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                    throw new Exception("Photo upload failed. User not created.");
                }
            }

            $stmt2 = $conn->prepare("INSERT INTO doctors 
                (user_id, name, email, department, designation, council_number, phone, photo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("isssssss", $user_id, $name, $email, $department, $designation, $council_number, $phone, $photoFilename);
            if (!$stmt2->execute()) throw new Exception("Could not create doctor record.");
            $doctor_id = $stmt2->insert_id;
            $stmt2->close();

            if (!empty($_POST['degree']) && is_array($_POST['degree'])) {
                for ($i = 0; $i < count($_POST['degree']); $i++) {
                    $deg = trim($_POST['degree'][$i]);
                    $inst = trim($_POST['institution'][$i]);
                    $yr = trim($_POST['year_of_completion'][$i]);
                    if ($deg != "" && $inst != "" && $yr != "") {
                        $stmt3 = $conn->prepare("INSERT INTO doctor_education 
                            (doctor_id, degree, institution, year_of_completion) 
                            VALUES (?, ?, ?, ?)");
                        $stmt3->bind_param("isss", $doctor_id, $deg, $inst, $yr);
                        if (!$stmt3->execute()) throw new Exception("Could not insert education record.");
                        $stmt3->close();
                    }
                }
            }
        } elseif ($role == 'nurse') {
            $stmt2 = $conn->prepare("INSERT INTO nurses (user_id, name, email, phone) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("isss", $user_id, $name, $email, $phone);
            if (!$stmt2->execute()) throw new Exception("Could not create nurse record.");
            $stmt2->close();
        } elseif ($role == 'admin') {
            $stmt2 = $conn->prepare("INSERT INTO admins (user_id, name, email) VALUES (?, ?, ?)");
            $stmt2->bind_param("iss", $user_id, $name, $email);
            if (!$stmt2->execute()) throw new Exception("Could not create admin record.");
            $stmt2->close();
        }

        $conn->commit();
        $success = "User created successfully.";
    } catch (Exception $e) {
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
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Roboto','Segoe UI',sans-serif; }
    body { display:flex; height:100vh; color:#000; background:#f9f9fb; }

    .sidebar { width:250px; background:#015eac; color:#fff; padding:20px 0; display:flex; flex-direction:column; }
    .sidebar h2 { text-align:center; margin-bottom:30px; font-size:1.5rem; font-weight:bold; }
    .sidebar a { display:block; padding:12px 20px; color:#fff; text-decoration:none; font-weight:500; }
    .sidebar a:hover, .sidebar a.active { background:#004d91; border-left:4px solid #fff; }

    .main { flex:1; padding:20px; overflow-y:auto; }
    .topbar { background:#fff; padding:15px 20px; border-radius:12px; margin-bottom:20px;
              display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 10px rgba(0,0,0,0.05);}
    .topbar h1 { color:#015eac; font-size:1.6rem; }
    .menu-toggle { display:none; font-size:1.5rem; cursor:pointer; color:#015eac; }

    .form-card { background:#fff; padding:2rem; border-radius:16px; box-shadow:0 6px 16px rgba(0,0,0,0.08); max-width:600px; margin:auto; }
    .form-card h2 { margin-bottom:1rem; font-size:24px; color:#015eac; text-align:center; }
    .form-card form { display:flex; flex-direction:column; gap:1rem; }
    .form-card input, .form-card select { padding:0.8rem 1rem; border:1px solid #ccc; border-radius:8px; }
    .form-card input:focus, .form-card select:focus { border-color:#015eac; outline:none; }
    .doctor-fields { display:none; flex-direction:column; gap:1rem; }
    .education-entry { display:flex; flex-direction:column; gap:0.5rem; margin-bottom:1rem; }
    .add-btn { background:#f0f0f0; border:1px dashed #999; padding:0.6rem; border-radius:6px; cursor:pointer; }
    .add-btn:hover { background:#e0e0e0; }
    .create-btn { background:#015eac; color:#fff; border:none; padding:0.9rem; border-radius:8px; cursor:pointer; }
    .create-btn:hover { background:#1f4fbf; }
    .message { text-align:center; font-size:0.95rem; }
    .success { color:green; }
    .error { color:red; }

    @media(max-width:992px){
      body { flex-direction:column; }
      .sidebar { position:fixed; top:0; left:0; height:100%; transform:translateX(-100%); z-index:1000; transition:transform 0.3s; }
      .sidebar.show { transform:translateX(0); }
      .menu-toggle { display:block; margin-right:15px; }
      .topbar { justify-content:flex-start; gap:15px; }
    }
  </style>
</head>
<body>
  <div class="sidebar" id="sidebar">
    <h2>Admin Panel</h2>
    <a href="#">Dashboard</a>
    <a href="#" class="active">Add Users</a>
    <a href="#">Manage Users</a>
    <a href="#">Logout</a>
  </div>

  <div class="main">
    <div class="topbar">
      <span class="menu-toggle" onclick="toggleSidebar()">☰</span>
      <h1>Add Users</h1>
    </div>

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

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("show");
}
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
