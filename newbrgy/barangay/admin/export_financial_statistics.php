<?php
require_once '../db_connection.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="financial_statistics_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Write headers
fputcsv($output, ['Document Type', 'Total Amount', 'Number of Transactions']);

// Fetch document type statistics
$stmt = $conn->prepare("SELECT document_type, SUM(price) as total_amount, COUNT(*) as count FROM orders GROUP BY document_type");
$stmt->execute();
$result = $stmt->get_result();

// Write data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        ucwords(str_replace('_', ' ', $row['document_type'])),
        $row['total_amount'],
        $row['count']
    ]);
}

// Add two blank rows for separation
fputcsv($output, []);
fputcsv($output, []);

// Add monthly statistics headers
fputcsv($output, ['Month', 'Total Revenue']);

$stmt = $conn->prepare("SELECT month, total_amount FROM monthly_salary ORDER BY month DESC LIMIT 12");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['month'],
        $row['total_amount']
    ]);
}

fclose($output);
exit();
?>