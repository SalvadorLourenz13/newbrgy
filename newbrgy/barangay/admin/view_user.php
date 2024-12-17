<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id === 0) {
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: admin_dashboard.php");
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident's Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .profile-card {
            background-color: #f8f9fa;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 700px;
            margin: auto;
        }

        .profile-card img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
        }

        .profile-card h2 {
            margin-top: 20px;
            font-size: 30px;
            font-weight: bold;
        }

        .profile-card h3 {
            margin-top: 5px;
            font-size: 18px;
            color: #6c757d;
        }

        .profile-card dl dt {
            font-weight: bold;
            width: 150px;
        }

        .profile-card dl dd {
            margin-bottom: 10px;
            font-size: 16px;
        }

        .contact-info, .family-info {
            margin-top: 20px;
            width: 100%;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 10px;
        }

        .contact-info h5, .family-info h5 {
            margin-bottom: 10px;
            font-size: 20px;
            color: #495057;
        }

        .back-btn {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="profile-card">
            <!-- Profile Picture -->
            <?php if (!empty($user['photo'])): ?>
                <img src="../<?php echo htmlspecialchars($user['photo']); ?>" alt="Profile Photo">
            <?php else: ?>
                <img src="../images/default-profile.png" alt="Default Profile Photo">
            <?php endif; ?>

            <!-- User Info -->
            <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <h3><?php echo htmlspecialchars($user['barangay']) . ', ' . htmlspecialchars($user['city']); ?></h3>

            <!-- User Details -->
            <dl class="row">
                <dt class="col-sm-3">Birthdate:</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($user['birthdate']); ?></dd>

                <dt class="col-sm-3">Purok:</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($user['purok']); ?></dd>

                <dt class="col-sm-3">Zipcode:</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($user['zipcode']); ?></dd>

                <dt class="col-sm-3">Account Created:</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($user['created_at']); ?></dd>

                <dt class="col-sm-3">Last Updated:</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($user['updated_at']); ?></dd>
            </dl>

            <!-- Back to Dashboard Button -->
            <div class="back-btn">
                <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
