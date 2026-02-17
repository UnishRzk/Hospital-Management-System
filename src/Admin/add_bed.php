<?php
session_start();
include("../config/db.php");

// Restrict access to admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num_beds = (int)$_POST['num_beds'];
    $type = $_POST['type'];
    $status = $_POST['status'];

    if ($num_beds > 0 && $type && $status) {
        $stmt = $conn->prepare("INSERT INTO beds (type, status) VALUES (?, ?)");
        $stmt->bind_param("ss", $type, $status);

        $success_count = 0;
        for ($i = 0; $i < $num_beds; $i++) {
            if ($stmt->execute()) {
                $success_count++;
            }
        }

        $stmt->close();

        if ($success_count > 0) {
            // Redirect after success
            header("Location: manage_beds.php?success=" . urlencode("$success_count bed(s) added successfully."));
            exit();
        } else {
            $error = "Failed to add beds.";
        }
    } else {
        $error = "All fields are required.";
    }
}


// Helper function for escaping
function e($val) {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Add Bed | Admin Panel</title>
<style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: Roboto, Segoe UI, sans-serif;
  background: #cfe1f0;
  color: #0b1e2d;
  line-height: 1.5;
}

.form-container {
  max-width: 600px;
  margin: 3rem auto;
  background: #fff;
  padding: 2rem 2.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

h2 {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #015eac;
  font-size: 1.8rem;
  font-weight: 800;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
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
.form-group select {
  padding: 0.6rem 0.8rem;
  border: 1px solid #c5d2df;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color .3s, box-shadow .3s;
}

.form-group input:focus,
.form-group select:focus {
  border-color: #015eac;
  box-shadow: 0 0 0 3px rgba(1,94,172,0.15);
  outline: none;
}

button {
  width: 100%;
  padding: 0.8rem;
  background: #015eac;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background .3s;
}

button:hover {
  background: #014f91;
}

.alert {
  text-align: center;
  margin-bottom: 1rem;
  padding: 0.7rem;
  border-radius: 6px;
  font-weight: 600;
}

.alert-error {
  background: #f8d7da;
  color: #721c24;
}
</style>
</head>
<body>

<div class="form-container">
  <h2>Add Beds</h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo e($error); ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-grid">

      <div class="form-group">
        <label for="num_beds">Number of Beds</label>
        <input type="number" name="num_beds" id="num_beds" min="1" placeholder="Enter number of beds" required>
      </div>

      <div class="form-group">
        <label for="type">Bed Type</label>
        <select name="type" id="type" required>
          <option value="">Select type</option>
          <option value="General">General</option>
          <option value="Semi-Private">Semi-Private</option>
          <option value="Private">Private</option>
        </select>
      </div>

      <div class="form-group">
        <label for="status">Status</label>
        <select name="status" id="status" required>
          <option value="">Select status</option>
          <option value="Empty">Empty</option>
          <option value="Reserved">Reserved</option>
          <option value="Occupied">Occupied</option>
          <option value="Out of Order">Out of Order</option>
        </select>
      </div>

      <button type="submit">Add Beds</button>

    </div>
  </form>
</div>

</body>
</html>
