<?php
$page_title = "Admin Dashboard";
$active_page = "dashboard";
require_once 'includes/header.php';
require_once '../db_connection.php';

// Fetch some quick statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_orders FROM orders");
$stmt->execute();
$total_orders = $stmt->get_result()->fetch_assoc()['total_orders'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'Pending'");
$stmt->execute();
$pending_orders = $stmt->get_result()->fetch_assoc()['pending_orders'];
$stmt->close();

$stmt = $conn->prepare("SELECT SUM(price) as total_revenue FROM orders");
$stmt->execute();
$total_revenue = $stmt->get_result()->fetch_assoc()['total_revenue'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['total_users'];
$stmt->close();

?>

<div class="row">
    <div class="col-md-12">
        <h1 class="text-center mb-4">Admin Dashboard</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <p class="card-text display-4"><?php echo $total_orders; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Pending Orders</h5>
                <p class="card-text display-4"><?php echo $pending_orders; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <p class="card-text display-4">₱<?php echo number_format($total_revenue, 2); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <p class="card-text display-4"><?php echo $total_users; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quick Actions</h5>
                <div class="list-group">
                    <a href="view_orders.php" class="list-group-item list-group-item-action">View All Orders</a>
                    <a href="manage_documents.php" class="list-group-item list-group-item-action">Manage Documents</a>
                    <a href="financial_statistics.php" class="list-group-item list-group-item-action">View Financial
                        Statistics</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Recent Orders</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Document Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM orders ORDER BY order_date DESC LIMIT 5");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($order = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['document_type']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php
                                        switch ($order['status']) {
                                            case 'Pending':
                                                echo 'warning';
                                                break;
                                            case 'Validated':
                                                echo 'success';
                                                break;
                                            case 'Rejected':
                                                echo 'danger';
                                                break;
                                            case 'Completed':
                                                echo 'info';
                                                break;
                                            default:
                                                echo 'secondary';
                                        }
                                        ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view_order.php?id=<?php echo $order['id']; ?>"
                                            class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>