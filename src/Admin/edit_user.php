<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Validate Input
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID.");
}
$user_id = (int)$_GET['id'];

// Fetch User 
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found.");
}

$role = $user['role'];

// Fetch role-specific data
$roleData = [];
if ($role == 'doctor') {
    $stmt = $conn->prepare("SELECT * FROM doctors WHERE user_id=?");
} elseif ($role == 'nurse') {
    $stmt = $conn->prepare("SELECT * FROM nurses WHERE user_id=?");
} elseif ($role == 'patient') {
    $stmt = $conn->prepare("SELECT * FROM patients WHERE user_id=?");
} elseif ($role == 'admin') {
    $stmt = $conn->prepare("SELECT * FROM admins WHERE user_id=?");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$roleData = $stmt->get_result()->fetch_assoc();

// For doctors: fetch education records
$education = [];
if ($role == 'doctor' && $roleData) {
    $stmt = $conn->prepare("SELECT * FROM doctor_education WHERE doctor_id=?");
    $stmt->bind_param("i", $roleData['doctor_id']);
    $stmt->execute();
    $education = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Handle Form Submission 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];

    $conn->begin_transaction();
    try {
        // Update users table
        $stmt = $conn->prepare("UPDATE users SET username=?, password=? WHERE user_id=?");
        $stmt->bind_param("ssi", $username, $password, $user_id);
        $stmt->execute();

        // Role-specific updates
        if ($role == 'doctor') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $department = $_POST['department'];
            $designation = $_POST['designation'];
            $council = $_POST['council_number'];

            // Handle photo upload
            $photo = $roleData['photo'];
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = "../images/doctors/";
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $photo = "doctor_" . time() . "_" . uniqid() . "." . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photo);
            }

            $stmt = $conn->prepare("UPDATE doctors 
                SET name=?, email=?, phone=?, department=?, designation=?, council_number=?, photo=? 
                WHERE user_id=?");
            $stmt->bind_param("sssssssi", $name, $email, $phone, $department, $designation, $council, $photo, $user_id);
            $stmt->execute();

            // Update education: delete old, insert new
            $doctor_id = $roleData['doctor_id'];
            $conn->query("DELETE FROM doctor_education WHERE doctor_id=$doctor_id");

            if (!empty($_POST['degree'])) {
                for ($i = 0; $i < count($_POST['degree']); $i++) {
                    $deg = trim($_POST['degree'][$i]);
                    $inst = trim($_POST['institution'][$i]);
                    $yr = trim($_POST['year_of_completion'][$i]);
                    if ($deg && $inst && $yr) {
                        $stmt = $conn->prepare("INSERT INTO doctor_education 
                            (doctor_id, degree, institution, year_of_completion) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("isss", $doctor_id, $deg, $inst, $yr);
                        $stmt->execute();
                    }
                }
            }
        } elseif ($role == 'nurse') {
            $stmt = $conn->prepare("UPDATE nurses SET name=?, email=?, phone=? WHERE user_id=?");
            $stmt->bind_param("sssi", $_POST['name'], $_POST['email'], $_POST['phone'], $user_id);
            $stmt->execute();
        } elseif ($role == 'patient') {
            $stmt = $conn->prepare("UPDATE patients SET name=?, email=?, phone=? WHERE user_id=?");
            $stmt->bind_param("sssi", $_POST['name'], $_POST['email'], $_POST['phone'], $user_id);
            $stmt->execute();
        } elseif ($role == 'admin') {
            $stmt = $conn->prepare("UPDATE admins SET name=?, email=? WHERE user_id=?");
            $stmt->bind_param("ssi", $_POST['name'], $_POST['email'], $user_id);
            $stmt->execute();
        }

        $conn->commit();
        header("Location: manage_users.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <meta charset="UTF-8">
    <style>
        body { 
            font-family: Arial; 
            background: #f4f4f9;
            padding: 20px; 
        }

        .form-container { 
            max-width: 700px; 
            margin: auto; 
            background: white; 
            padding: 20px; 
            border-radius: 12px; 
        }

        h2 { 
            color: #015eac;
         }

        input, select { 
            width: 100%; 
            padding: 10px; 
            margin: 8px 0; 
            border-radius: 6px; 
            border: 1px solid #ccc; 
        }

        button { 
            background: #015eac; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
        }
        .edu-entry { 
            border: 1px dashed #aaa; 
            padding: 10px; 
            margin: 10px 0; 
        }
    </style>
    <script>
        function addEducation() {
            const container = document.getElementById('education-container');
            const div = document.createElement('div');
            div.className = 'edu-entry';
            div.innerHTML = `
                <input type="text" name="degree[]" placeholder="Degree">
                <input type="text" name="institution[]" placeholder="Institution">
                <input type="text" name="year_of_completion[]" placeholder="Year">
            `;
            container.appendChild(div);
        }
    </script>
</head>
<body>
<div class="form-container">
    <h2>Edit <?php echo ucfirst($role); ?> User</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">

        <label>Password (leave blank to keep unchanged):</label>
        <input type="password" name="password">

        <?php if ($role != 'admin') { ?>
            <label>Full Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($roleData['name'] ?? ''); ?>">

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($roleData['email'] ?? ''); ?>">

            <label>Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($roleData['phone'] ?? ''); ?>">
        <?php } else { ?>
            <label>Full Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($roleData['name'] ?? ''); ?>">

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($roleData['email'] ?? ''); ?>">
        <?php } ?>

        <?php if ($role == 'doctor') { ?>
            <label>Department:</label>
            <input type="text" name="department" value="<?php echo htmlspecialchars($roleData['department'] ?? ''); ?>">

            <label>Designation:</label>
            <input type="text" name="designation" value="<?php echo htmlspecialchars($roleData['designation'] ?? ''); ?>">

            <label>Council Number:</label>
            <input type="text" name="council_number" value="<?php echo htmlspecialchars($roleData['council_number'] ?? ''); ?>">

            <label>Profile Photo:</label>
            <input type="file" name="photo">
            <?php if (!empty($roleData['photo'])) { ?>
                <p>Current: <img src="../images/doctors/<?php echo $roleData['photo']; ?>" width="80"></p>
            <?php } ?>

            <h3>Educational Qualifications</h3>
            <div id="education-container">
                <?php foreach ($education as $edu) { ?>
                    <div class="edu-entry">
                        <input type="text" name="degree[]" value="<?php echo htmlspecialchars($edu['degree']); ?>" placeholder="Degree">
                        <input type="text" name="institution[]" value="<?php echo htmlspecialchars($edu['institution']); ?>" placeholder="Institution">
                        <input type="text" name="year_of_completion[]" value="<?php echo htmlspecialchars($edu['year_of_completion']); ?>" placeholder="Year">
                    </div>
                <?php } ?>
            </div>
            <button type="button" onclick="addEducation()">+ Add Education</button>
        <?php } ?>

        <br><br>
        <button type="submit">Done</button>
    </form>
</div>
</body>
</html>
