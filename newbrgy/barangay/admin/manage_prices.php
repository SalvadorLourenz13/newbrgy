<?php
$page_title = "Manage Document Prices";
$active_page = "manage_prices";
require_once 'includes/header.php';
require_once '../db_connection.php';


if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = '';

// Fetch current prices
$stmt = $conn->prepare("SELECT * FROM document_prices");
$stmt->execute();
$result = $stmt->get_result();
$prices = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['prices'] as $document_type => $price) {
        $stmt = $conn->prepare("INSERT INTO document_prices (document_type, price) VALUES (?, ?) ON DUPLICATE KEY UPDATE price = ?");
        $stmt->bind_param("sdd", $document_type, $price, $price);
        $stmt->execute();
        $stmt->close();
    }
    $message = "Prices updated successfully!";
}

?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="neumorphic p-4">
                <h1 class="text-center mb-4">Manage Document Prices</h1>
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <?php foreach ($prices as $price): ?>
                        <div class="mb-3">
                            <label for="<?php echo $price['document_type']; ?>"
                                class="form-label"><?php echo ucwords(str_replace('_', ' ', $price['document_type'])); ?>:</label>
                            <input type="number" step="0.01" class="form-control"
                                id="<?php echo $price['document_type']; ?>"
                                name="prices[<?php echo $price['document_type']; ?>]" value="<?php echo $price['price']; ?>"
                                required>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-primary btn-neumorphic w-100">Update Prices</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>