<?php
$page_title = "Financial Statistics";
$active_page = "financial_statistics";
$extra_css = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
require_once 'includes/header.php';
require_once '../db_connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch monthly statistics
$stmt = $conn->prepare("SELECT * FROM monthly_salary ORDER BY month DESC LIMIT 12");
$stmt->execute();
$result = $stmt->get_result();
$monthly_stats = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch total monthly revenue
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(price) AS total_amount 
    FROM orders 
    GROUP BY month 
    ORDER BY month DESC
    LIMIT 12
");
$stmt->execute();
$result = $stmt->get_result();
$monthly_revenue = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch document type statistics
$stmt = $conn->prepare("SELECT document_type, SUM(price) as total_amount, COUNT(*) as count FROM orders GROUP BY document_type");
$stmt->execute();
$result = $stmt->get_result();
$document_stats = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="neumorphic p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mb-0"></h1>
                    <div>
                        <!-- Export to CSV and Print buttons below each other -->
                        <a href="export_financial_statistics.php" class="btn btn-success mb-2">
                            Export to CSV
                        </a>
                        <button class="btn btn-primary" onclick="window.print()">Print</button>
                    </div>
                </div>

                <!-- Display Total Monthly Revenue -->
                <div class="mb-4">
                    <h4>Total Revenue per Month:</h4>
                    <ul>
                        <?php foreach ($monthly_revenue as $revenue): ?>
                            <li><strong><?php echo $revenue['month']; ?>:</strong> ₱<?php echo number_format($revenue['total_amount'], 2); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Monthly Revenue Chart -->
                <div class="mb-4">
                    <canvas id="monthlyChart" class="chart-container"></canvas>
                </div>

                <!-- Document Type Statistics -->
                <h2 class="mt-4">Document Type Statistics</h2>
                <div class="mb-4">
                    <canvas id="documentChart" class="chart-container"></canvas>
                </div>

                <!-- Detailed Statistics -->
                <h2 class="mt-4">Detailed Statistics</h2>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Document Type</th>
                                <th>Total Amount</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($document_stats as $stat): ?>
                                <tr>
                                    <td><?php echo ucwords(str_replace('_', ' ', $stat['document_type'])); ?></td>
                                    <td>₱<?php echo number_format($stat['total_amount'], 2); ?></td>
                                    <td><?php echo $stat['count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Monthly Revenue Chart
    var monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    var monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column(array_reverse($monthly_stats), 'month')); ?>,
            datasets: [{
                label: 'Monthly Revenue',
                data: <?php echo json_encode(array_column(array_reverse($monthly_stats), 'total_amount')); ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Document Type Statistics Chart
    var documentCtx = document.getElementById('documentChart').getContext('2d');
    var documentChart = new Chart(documentCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_map(function ($item) {
                return ucwords(str_replace('_', ' ', $item['document_type'])); }, $document_stats)); ?>,
            datasets: [{
                label: 'Total Amount',
                data: <?php echo json_encode(array_column($document_stats, 'total_amount')); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<style>
    .chart-container {
        width: 100% !important;
        max-width: 600px;
        height: 900px;
        margin: 0 auto;
    }
</style>

<?php require_once 'includes/footer.php'; ?>
