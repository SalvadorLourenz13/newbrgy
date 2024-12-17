<?php
session_start();
require_once 'db_connection.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];

    // Query the database to match the password
    $stmt = $conn->prepare("SELECT * FROM users WHERE password = ?");
    $stmt->bind_param("s", $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Successful login
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        header("Location: neworder.php");
        exit;
    } else {
        $error_message = "Invalid password. Please try again.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Barangay Request System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="neumorphic p-4">
                    <h2 class="text-center mb-4">Resident Login</h2>
                    <?php
                    if (!empty($error_message)) {
                        echo "<div class='alert alert-danger'>$error_message</div>";
                    }
                    ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                          
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="form-text text-muted">input given password to proceed</small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-neumorphic w-100">Login</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="register.php">Register as New Resident</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
