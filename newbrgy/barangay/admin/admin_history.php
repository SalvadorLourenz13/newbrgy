<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$orders = [];
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$purok_filter = isset($_GET['purok']) ? $_GET['purok'] : '';
$address_filter = isset($_GET['address']) ? $_GET['address'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$query = "SELECT o.*, u.full_name, u.purok, u.barangay, u.city, u.zipcode 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          WHERE 1=1";

$params = [];

if ($start_date && $end_date) {
    $query .= " AND o.order_date BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

if ($status_filter) {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
}

if ($purok_filter) {
    $query .= " AND u.purok = ?";
    $params[] = $purok_filter;
}

if ($address_filter) {
    $query .= " AND CONCAT(u.barangay, ' ', u.city, ' ', u.zipcode) LIKE ?";
    $params[] = '%' . $address_filter . '%';
}

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin History - Barangay Request System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styles.css">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .print-only {
                display: block;
            }
        }
        .print-only {
            display: none;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light no-print">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="../assets/brgy.png" alt="Barangay Logo" class="img-fluid">
                <span class="d-none d-sm-inline">Barangay Request System</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_history.php">History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_logout.php">Log Out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="neumorphic">
                    <h1 class="text-center mb-4">Order History</h1>
                    <form action="" method="GET" class="mb-4 no-print">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All</option>
                                    <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Completed" <?php echo $status_filter == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="Canceled" <?php echo $status_filter == 'Canceled' ? 'selected' : ''; ?>>Canceled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="purok" class="form-label">Purok</label>
                                <input type="text" class="form-control" id="purok" name="purok" value="<?php echo $purok_filter; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo $address_filter; ?>">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-neumorphic w-100">Filter</button>
                            </div>
                        </div>
                    </form>
                    <form id="print-form">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="no-print">Select</th>
                                        <th>Order ID</th>
                                        <th>Full Name</th>
                                        <th>Purok</th>
                                        <th>Document Type</th>
                                        <th>Copies</th>
                                        <th>Order Date</th>
                                        <th>Status</th>
                                        <th>Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="no-print">
                                                <input type="checkbox" name="selected_orders[]" value="<?php echo $order['id']; ?>">
                                            </td>
                                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                                            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($order['purok']); ?></td>
                                            <td><?php echo htmlspecialchars($order['document_type']); ?></td>
                                            <td><?php echo htmlspecialchars($order['copies']); ?></td>
                                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                                            <td><?php echo htmlspecialchars($order['barangay'] . ', ' . $order['city'] . ' ' . $order['zipcode']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 no-print">
                            <button type="button" class="btn btn-primary btn-neumorphic" onclick="printSelected()">Print Selected</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="print-only">
        <h2 class="text-center mb-4">Barangay Request System - Order Report</h2>
        <p>Date Range: <?php echo $start_date ? $start_date : 'All'; ?> to <?php echo $end_date ? $end_date : 'All'; ?></p>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Full Name</th>
                    <th>Purok</th>
                    <th>Document Type</th>
                    <th>Copies</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['purok']); ?></td>
                        <td><?php echo htmlspecialchars($order['document_type']); ?></td>
                        <td><?php echo htmlspecialchars($order['copies']); ?></td>
                        <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td><?php echo htmlspecialchars($order['barangay'] . ', ' . $order['city'] . ' ' . $order['zipcode']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function printSelected() {
            var selectedOrders = [];
            var checkboxes = document.querySelectorAll('input[name="selected_orders[]"]:checked');
            checkboxes.forEach(function(checkbox) {
                selectedOrders.push(checkbox.value);
            });

            if (selectedOrders.length > 0) {
                var form = document.getElementById('print-form');
                var newWindow = window.open('', '', 'width=800,height=600');
                newWindow.document.write('<html><head><title>Print Orders</title></head><body>');
                newWindow.document.write('<h1>Selected Orders</h1><table border="1"><thead><tr><th>Order ID</th><th>Full Name</th><th>Document Type</th><th>Status</th><th>Order Date</th></tr></thead><tbody>');

                selectedOrders.forEach(function(orderId) {
                    var row = document.querySelector('input[value="' + orderId + '"]').closest('tr');
                    var orderId = row.cells[1].textContent;
                    var fullName = row.cells[2].textContent;
                    var docType = row.cells[3].textContent;
                    var status = row.cells[4].textContent;
                    var orderDate = row.cells[5].textContent;

                    newWindow.document.write('<tr><td>' + orderId + '</td><td>' + fullName + '</td><td>' + docType + '</td><td>' + status + '</td><td>' + orderDate + '</td></tr>');
                });

                newWindow.document.write('</tbody></table>');
                newWindow.document.write('</body></html>');
                newWindow.print();
            } else {
                alert('Please select at least one order to print.');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
