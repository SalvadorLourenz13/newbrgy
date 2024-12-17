<?php
$page_title = "View Orders";
$active_page = "view_orders";
require_once 'includes/header.php';
require_once '../db_connection.php';

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

$total_records_query = $conn->query("SELECT COUNT(*) FROM orders");
$total_records = $total_records_query->fetch_row()[0];
$total_pages = ceil($total_records / $records_per_page);

// Fetch orders with pagination
$stmt = $conn->prepare("
    SELECT o.*, u.full_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $records_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h2 class="text-center mb-0">All Orders</h2>
                <!-- Print Button with Blue Color -->
                <button onclick="window.print()" class="btn btn-sm btn-primary float-end">Print</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User</th>
                                <th>Document Type</th>
                                <th>Copies</th>
                                <th>Price</th>
                                <th>Order Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['document_type']); ?></td>
                                    <td><?php echo htmlspecialchars($order['copies']); ?></td>
                                    <td>₱<?php echo number_format($order['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
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
                                            class="btn btn-sm btn-primary">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
