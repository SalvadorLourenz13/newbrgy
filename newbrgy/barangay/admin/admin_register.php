<?php
session_start();
require_once '../db_connection.php';

// Predefined Admin Key
define('ADMIN_KEY', 'adminkey2024');

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $admin_key = $_POST['admin_key'];

    if ($admin_key !== ADMIN_KEY) {
        $error_message = "Invalid Admin Key.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);

        if ($stmt->execute()) {
            $success_message = "Admin registration successful.";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Barangay Request System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="neumorphic p-4">
                    <h2 class="text-center mb-4">Admin Registration</h2>
                    <?php
                    if (!empty($error_message)) {
                        echo "<div class='alert alert-danger'>$error_message</div>";
                    }
                    if (!empty($success_message)) {
                        echo "<div class='alert alert-success'>$success_message</div>";
                    }
                    ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password:</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="admin_key" class="form-label">Admin Key:</label>
                            <input type="password" class="form-control" id="admin_key" name="admin_key" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-neumorphic w-100">Register Admin</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="admin_login.php">Back to Admin Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
