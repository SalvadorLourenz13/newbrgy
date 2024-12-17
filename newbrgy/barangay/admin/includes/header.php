<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Barangay Request System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styles.css">
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php">
                <img src="grande-removebg-preview.png" alt="Barangay Logo" class="img-fluid" style="max-height: 40px;">
                <span class="ms-2">Barangay Request System</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($active_page == 'dashboard') ? 'active' : ''; ?>" href="admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($active_page == 'view_orders') ? 'active' : ''; ?>" href="view_orders.php">View Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($active_page == 'manage_documents') ? 'active' : ''; ?>" href="manage_documents.php">Manage Documents</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($active_page == 'financial_statistics') ? 'active' : ''; ?>" href="financial_statistics.php">Financial Statistics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_logout.php">Log Out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">

