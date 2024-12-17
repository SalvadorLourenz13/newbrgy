<?php
$page_title = "Manage Documents";
$active_page = "manage_documents";
require_once 'includes/header.php';
require_once '../db_connection.php';

$message = '';

// Fetch current document prices
$stmt = $conn->prepare("SELECT * FROM document_prices ORDER BY document_type ASC");
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $document_type = $_POST['document_type'];
            $price = $_POST['price'];

            $stmt = $conn->prepare("INSERT INTO document_prices (document_type, price) VALUES (?, ?)");
            $stmt->bind_param("sd", $document_type, $price);

            if ($stmt->execute()) {
                $message = "Document added successfully!";
            } else {
                $message = "Error adding document. Please try again.";
            }
            $stmt->close();
        } elseif ($_POST['action'] == 'edit') {
            $id = $_POST['id'];
            $document_type = $_POST['document_type'];
            $price = $_POST['price'];

            $stmt = $conn->prepare("UPDATE document_prices SET document_type = ?, price = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("sdi", $document_type, $price, $id);

            if ($stmt->execute()) {
                $message = "Document updated successfully!";
            } else {
                $message = "Error updating document. Please try again.";
            }
            $stmt->close();
        } elseif ($_POST['action'] == 'delete') {
            $id = $_POST['id'];

            $stmt = $conn->prepare("DELETE FROM document_prices WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $message = "Document deleted successfully!";
            } else {
                $message = "Error deleting document. Please try again.";
            }
            $stmt->close();
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

$total_records_query = $conn->query("SELECT COUNT(*) FROM document_prices");
$total_records = $total_records_query->fetch_row()[0];
$total_pages = ceil($total_records / $records_per_page);

$stmt = $conn->prepare("SELECT * FROM document_prices ORDER BY document_type ASC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $records_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="container mt-4">
    <h1 class="text-center mb-4">Manage Documents</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
            Add New Document
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Document Type</th>
                    <th>Price</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $document): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($document['document_type']); ?></td>
                        <td>₱<?php echo number_format($document['price'], 2); ?></td>
                        <td><?php echo $document['created_at']; ?></td>
                        <td><?php echo $document['updated_at']; ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#editDocumentModal<?php echo $document['id']; ?>">
                                Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                data-bs-target="#deleteDocumentModal<?php echo $document['id']; ?>">
                                Delete
                            </button>
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

<!-- Add Document Modal -->
<div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDocumentModalLabel">Add New Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="document_type" class="form-label">Document Type</label>
                        <input type="text" class="form-control" id="document_type" name="document_type" required>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Document Modals -->
<?php foreach ($documents as $document): ?>
    <div class="modal fade" id="editDocumentModal<?php echo $document['id']; ?>" tabindex="-1"
        aria-labelledby="editDocumentModalLabel<?php echo $document['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDocumentModalLabel<?php echo $document['id']; ?>">Edit Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?php echo $document['id']; ?>">
                        <div class="mb-3">
                            <label for="document_type<?php echo $document['id']; ?>" class="form-label">Document
                                Type</label>
                            <input type="text" class="form-control" id="document_type<?php echo $document['id']; ?>"
                                name="document_type" value="<?php echo htmlspecialchars($document['document_type']); ?>"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="price<?php echo $document['id']; ?>" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="price<?php echo $document['id']; ?>"
                                name="price" value="<?php echo $document['price']; ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Delete Document Modals -->
<?php foreach ($documents as $document): ?>
    <div class="modal fade" id="deleteDocumentModal<?php echo $document['id']; ?>" tabindex="-1"
        aria-labelledby="deleteDocumentModalLabel<?php echo $document['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteDocumentModalLabel<?php echo $document['id']; ?>">Delete Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the document
                    "<?php echo htmlspecialchars($document['document_type']); ?>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $document['id']; ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require_once 'includes/footer.php'; ?>