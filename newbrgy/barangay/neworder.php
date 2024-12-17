<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$message = '';

// Fetch document prices
$stmt = $conn->prepare("SELECT * FROM document_price");
$stmt->execute();
$result = $stmt->get_result();
$prices = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $document_type = $_POST['document_type'];
    $purpose = $_POST['purpose'];
    $urgency = $_POST['urgency'];
    $pickup_method = $_POST['pickup_method'];
    $copies = $_POST['copies'];
    $representative_name = $pickup_method === 'Representative' ? $_POST['representative_name'] : null;
    $representative_relation = $pickup_method === 'Representative' ? $_POST['representative_relation'] : null;

    // Get the price for the selected document type
    $price = 0;
    foreach ($prices as $p) {
        if ($p['document_type'] == $document_type) {
            $price = $p['price'] * $copies;
            break;
        }
    }

    $stmt = $conn->prepare("INSERT INTO orders (user_id, document_type, purpose, urgency, pickup_method, representative_name, representative_relation, copies, status, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)");
    $stmt->bind_param("issssssis", $user_id, $document_type, $purpose, $urgency, $pickup_method, $representative_name, $representative_relation, $copies, $price);

    if ($stmt->execute()) {
        $message = "Order submitted successfully! Total price: ₱" . number_format($price, 2);

        // Update monthly salary
        $month = date('Y-m-01');
        $stmt = $conn->prepare("INSERT INTO monthly_salary (month, total_amount) VALUES (?, ?) ON DUPLICATE KEY UPDATE total_amount = total_amount + ?");
        $stmt->bind_param("sdd", $month, $price, $price);
        $stmt->execute();
    } else {
        $message = "Error submitting order. Please try again.";
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order - Barangay Request System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Carabatan Grande Document Request System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                      
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Back to Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger text-white" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="neumorphic">
                    <h1 class="text-center mb-4">New Document Request</h1>
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-info" role="alert">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <form id="orderForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-3">
                            <label for="document_type" class="form-label">Document Type:</label>
                            <select class="form-select" id="document_type" name="document_type" required onchange="updatePrice()">
                                <option value="">Select Document</option>
                                <?php foreach ($prices as $price): ?>
                                    <option value="<?php echo $price['document_type']; ?>" data-price="<?php echo $price['price']; ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $price['document_type'])); ?> - ₱<?php echo number_format($price['price'], 2); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="copies" class="form-label">Number of Copies:</label>
                            <input type="number" class="form-control" id="copies" name="copies" min="1" value="1" required onchange="updatePrice()">
                        </div>

                        <div class="mb-3">
                            <label for="total_price" class="form-label">Total Price:</label>
                            <input type="text" class="form-control" id="total_price" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="purpose" class="form-label">Purpose:</label>
                            <textarea class="form-control" id="purpose" name="purpose" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Urgency:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="urgency" id="urgency_regular" value="Regular" checked>
                                <label class="form-check-label" for="urgency_regular">Regular</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="urgency" id="urgency_rush" value="Rush">
                                <label class="form-check-label" for="urgency_rush">Rush</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pickup Method:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pickup_method" id="pickup_personal" value="Personal" checked onchange="toggleRepresentativeFields()">
                                <label class="form-check-label" for="pickup_personal">Personal</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pickup_method" id="pickup_representative" value="Representative" onchange="toggleRepresentativeFields()">
                                <label class="form-check-label" for="pickup_representative">Representative</label>
                            </div>
                        </div>

                        <div id="representative_fields" style="display: none;">
                            <div class="mb-3">
                                <label for="representative_name" class="form-label">Representative's Name:</label>
                                <input type="text" class="form-control" id="representative_name" name="representative_name">
                            </div>
                            <div class="mb-3">
                                <label for="representative_relation" class="form-label">Relation to Representative:</label>
                                <input type="text" class="form-control" id="representative_relation" name="representative_relation">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-neumorphic w-100">Submit Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleRepresentativeFields() {
            var representativeFields = document.getElementById('representative_fields');
            var pickupMethod = document.querySelector('input[name="pickup_method"]:checked').value;
            representativeFields.style.display = pickupMethod === 'Representative' ? 'block' : 'none';
        }

        function updatePrice() {
            var select = document.getElementById('document_type');
            var copies = document.getElementById('copies').value;
            var price = select.options[select.selectedIndex].getAttribute('data-price');
            var totalPrice = price * copies;
            document.getElementById('total_price').value = '₱' + parseFloat(totalPrice || 0).toFixed(2);
        }

        // Initialize price on page load
        updatePrice();
    </script>
</body>

</html>
