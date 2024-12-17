<?php
$page_title = "View Order";
$active_page = "view_orders";
require_once 'includes/header.php'; // Ensure session_start is in header.php
require_once '../db_connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id === 0) {
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch order details
$stmt = $conn->prepare("SELECT o.*, u.id AS user_id, u.full_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: admin_dashboard.php");
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_status = $_POST['status'];
    $admin_id = $_SESSION['admin_id']; // Get the admin's ID

    // Prepare SQL query to update both status and validated_by
    if ($new_status == 'Validated') {
        $update_stmt = $conn->prepare("UPDATE orders SET status = ?, validated_by = ? WHERE id = ?");
        $update_stmt->bind_param("sii", $new_status, $admin_id, $order_id);
    } else {
        // If status is not 'Validated', only update the status
        $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $order_id);
    }

    if ($update_stmt->execute()) {
        $message = "Order status updated successfully!";
        $order['status'] = $new_status;
    } else {
        $message = "Error updating order status. Please try again.";
    }
    $update_stmt->close();
}

$stmt->close();
$conn->close();
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="neumorphic">
                <h1 class="text-center mb-4">Order Details</h1>
                <?php if (!empty($message)): ?>
                    <div class="alert alert-info" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <dl class="row">
                    <dt class="col-sm-3">Order ID:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($order['id']); ?></dd>

                    <dt class="col-sm-3">User:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($order['full_name']); ?></dd>

                    <dt class="col-sm-3">Document Type:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($order['document_type']); ?></dd>

                    <dt class="col-sm-3">Purpose:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($order['purpose']); ?></dd>

                    <dt class="col-sm-3">Urgency:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($order['urgency']); ?></dd>

                    <dt class="col-sm-3">Pickup Method:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($order['pickup_method']); ?></dd>

                    <?php if ($order['pickup_method'] === 'Representative'): ?>
                        <dt class="col-sm-3">Representative:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($order['representative_name']); ?></dd>

                        <dt class="col-sm-3">Relation:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($order['representative_relation']); ?></dd>
                    <?php endif; ?>

                    <dt class="col-sm-3">Status:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($order['status']); ?></dd>
                </dl>

                <!-- Button to view user details -->
                <div class="text-center mt-3">
                    <a href="view_user.php?id=<?php echo $order['user_id']; ?>" class="btn btn-info">View Resident's
                        Details</a>
                </div>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="status" class="form-label">Update Status:</label>
                        <select class="form-select" id="status" name="status">
                            <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending
                            </option>
                            <option value="Rejected" <?php echo $order['status'] == 'Rejected' ? 'selected' : ''; ?>>
                                Rejected</option>
                            <option value="Validated" <?php echo $order['status'] == 'Validated' ? 'selected' : ''; ?>>
                                Validate</option>
                            <option value="Completed" <?php echo $order['status'] == 'Completed' ? 'selected' : ''; ?>>
                                Completed</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-neumorphic">Update Status</button>
                </form>

                <!-- Back to Dashboard Button -->
                <div class="mt-3 text-center">
                    <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
