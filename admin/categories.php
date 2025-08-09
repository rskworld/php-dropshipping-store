<?php
$adminPageTitle = 'Categories';
$currentAdminPage = 'categories';
require_once 'header.php';
require_once '../db_connect.php';

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name']);
        if (!empty($name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
                $stmt->execute(['name' => $name]);
                $message = 'Category added successfully!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error adding category: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    } elseif (isset($_POST['edit_category'])) {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $name = trim($_POST['name']);
        if ($id && !empty($name)) {
            try {
                $stmt = $pdo->prepare("UPDATE categories SET name = :name WHERE id = :id");
                $stmt->execute(['name' => $name, 'id' => $id]);
                $message = 'Category updated successfully!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error updating category: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    } elseif (isset($_POST['delete_category'])) {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $message = 'Category deleted successfully!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error deleting category: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    }
}

// Fetch categories
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Error fetching categories: ' . $e->getMessage();
    $message_type = 'danger';
}
?>

<div class="container-fluid">
    <h1 class="mb-4">Category Management</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>" role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Add New Category</div>
                <div class="card-body">
                    <form method="POST" action="categories.php">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Existing Categories</div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($categories as $cat): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <form method="POST" action="categories.php" class="d-flex flex-grow-1">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <input type="text" class="form-control me-2" name="name" value="<?= htmlspecialchars($cat['name']) ?>">
                                    <button type="submit" name="edit_category" class="btn btn-sm btn-info me-2">Update</button>
                                    <button type="submit" name="delete_category" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
