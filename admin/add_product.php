<?php
$adminPageTitle = 'Add Product';
$currentAdminPage = 'products';
require_once 'header.php';
require_once '../db_connect.php';

$message = '';
$message_type = '';

// Define upload directory
$upload_dir = '../uploads/products/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $details = trim($_POST['details'] ?? ''); // New details field
    $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
    $icon = trim($_POST['icon'] ?? '');

    $main_image_path = '';
    $additional_image_paths = [];

    // Handle main image upload
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['main_image']['tmp_name'];
        $file_name = basename($_FILES['main_image']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid('product_main_', true) . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;
            if (move_uploaded_file($file_tmp_name, $destination)) {
                $main_image_path = 'uploads/products/' . $new_file_name;
            } else {
                $message = 'Failed to upload main image.';
                $message_type = 'danger';
            }
        } else {
            $message = 'Invalid main image file type. Only JPG, JPEG, PNG, GIF are allowed.';
            $message_type = 'danger';
        }
    } else if (!isset($_FILES['main_image']) || $_FILES['main_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $message = 'Error uploading main image: ' . $_FILES['main_image']['error'];
        $message_type = 'danger';
    }

    if (empty($name) || $price === false || $price < 0 || empty($main_image_path)) {
        $message = 'Please fill in all required fields (Name, Price, Main Image) and ensure price is a valid number.';
        $message_type = 'danger';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO products (name, description, details, price, icon, main_image) VALUES (:name, :description, :details, :price, :icon, :main_image)");
            $stmt->execute([
                'name' => $name,
                'description' => $description,
                'details' => $details, // Save details
                'price' => $price,
                'icon' => $icon,
                'main_image' => $main_image_path
            ]);
            $product_id = $pdo->lastInsertId();

            // Handle additional images upload
            if (isset($_FILES['additional_images']) && is_array($_FILES['additional_images']['tmp_name'])) {
                foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['additional_images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_name = basename($_FILES['additional_images']['name'][$key]);
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        if (in_array($file_ext, $allowed_ext)) {
                            $new_file_name = uniqid('product_add_', true) . '.' . $file_ext;
                            $destination = $upload_dir . $new_file_name;
                            if (move_uploaded_file($tmp_name, $destination)) {
                                $additional_image_paths[] = 'uploads/products/' . $new_file_name;
                                $stmt_img = $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (:product_id, :image_path)");
                                $stmt_img->execute([
                                    'product_id' => $product_id,
                                    'image_path' => 'uploads/products/' . $new_file_name
                                ]);
                            } else {
                                $message .= ' Failed to upload additional image: ' . $file_name . '.';
                                $message_type = 'warning';
                            }
                        } else {
                            $message .= ' Invalid additional image file type for: ' . $file_name . '.';
                            $message_type = 'warning';
                        }
                    }
                }
            }

            $pdo->commit();
            $message = 'Product added successfully!';
            $message_type = 'success';
            // Clear form fields after successful submission
            $name = $description = $details = $price = $icon = $main_image_path = '';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = 'Error adding product: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}
?>

<div class="container-fluid">
    <h1 class="mb-4">Add New Product</h1>
    <div class="card">
        <div class="card-header">
            Product Details
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="add_product.php" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Short Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($description ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="details" class="form-label">Detailed Description</label>
                    <textarea class="form-control" id="details" name="details" rows="5"><?= htmlspecialchars($details ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($price ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="icon" class="form-label">Icon (Font Awesome class, e.g., fas fa-laptop) <small class="text-muted">(Optional, for legacy use)</small></label>
                    <input type="text" class="form-control" id="icon" name="icon" value="<?= htmlspecialchars($icon ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="main_image" class="form-label">Main Product Image <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="main_image" name="main_image" accept="image/*" required>
                    <small class="form-text text-muted">Upload the primary image for the product.</small>
                </div>
                <div class="mb-3">
                    <label for="additional_images" class="form-label">Additional Product Images</label>
                    <input type="file" class="form-control" id="additional_images" name="additional_images[]" accept="image/*" multiple>
                    <small class="form-text text-muted">Upload multiple images for the product gallery.</small>
                </div>
                <button type="submit" class="btn btn-primary">Add Product</button>
                <a href="products.php" class="btn btn-secondary">Back to Products</a>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
