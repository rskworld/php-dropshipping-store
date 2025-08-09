<?php
$adminPageTitle = 'Edit Product';
$currentAdminPage = 'products';
require_once 'header.php';
require_once '../db_connect.php';

$message = '';
$message_type = '';
$product = null;
$additional_images = [];

// Define upload directory
$upload_dir = '../uploads/products/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Function to delete image from file system
function deleteImageFile($path) {
    if (file_exists($path) && is_file($path)) {
        unlink($path);
    }
}

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id === false) {
        $message = 'Invalid product ID.';
        $message_type = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                $message = 'Product not found.';
                $message_type = 'danger';
            } else {
                // Fetch additional images
                $stmt_img = $pdo->prepare("SELECT id, image_path FROM product_images WHERE product_id = :product_id ORDER BY sort_order, id");
                $stmt_img->execute(['product_id' => $id]);
                $additional_images = $stmt_img->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $details = trim($_POST['details'] ?? ''); // New details field
    $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
    $icon = trim($_POST['icon'] ?? '');

    // Re-fetch product to get current main_image path for potential deletion
    $current_product = null;
    if ($id) {
        $stmt = $pdo->prepare("SELECT main_image FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $current_product = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($id === false || empty($name) || $price === false || $price < 0) {
        $message = 'Please fill in all required fields (Name, Price) and ensure price is a valid number.';
        $message_type = 'danger';
    } else {
        try {
            $pdo->beginTransaction();

            $main_image_updated = false;
            $new_main_image_path = $current_product['main_image'] ?? '';

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
                        // Delete old main image if it exists and is not a placeholder
                        if ($current_product && !empty($current_product['main_image']) && strpos($current_product['main_image'], 'placehold.co') === false) {
                            deleteImageFile('../../' . $current_product['main_image']);
                        }
                        $new_main_image_path = 'uploads/products/' . $new_file_name;
                        $main_image_updated = true;
                    } else {
                        $message = 'Failed to upload new main image.';
                        $message_type = 'danger';
                    }
                } else {
                    $message = 'Invalid main image file type. Only JPG, JPEG, PNG, GIF are allowed.';
                    $message_type = 'danger';
                }
            } else if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $message = 'Error uploading main image: ' . $_FILES['main_image']['error'];
                $message_type = 'danger';
            }

            // Update product details
            $sql = "UPDATE products SET name = :name, description = :description, details = :details, price = :price, icon = :icon";
            if ($main_image_updated) {
                $sql .= ", main_image = :main_image";
            }
            $sql .= " WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $params = [
                'name' => $name,
                'description' => $description,
                'details' => $details,
                'price' => $price,
                'icon' => $icon,
                'id' => $id
            ];
            if ($main_image_updated) {
                $params['main_image'] = $new_main_image_path;
            }
            $stmt->execute($params);

            // Handle additional images upload
            if (isset($_FILES['additional_images']) && is_array($_FILES['additional_images']['tmp_name'])) {
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif']; // Define allowed extensions here
                foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['additional_images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_name = basename($_FILES['additional_images']['name'][$key]);
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        if (in_array($file_ext, $allowed_ext)) {
                            $new_file_name = uniqid('product_add_', true) . '.' . $file_ext;
                            $destination = $upload_dir . $new_file_name;
                            if (move_uploaded_file($tmp_name, $destination)) {
                                $stmt_img = $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (:product_id, :image_path)");
                                $stmt_img->execute([
                                    'product_id' => $id,
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

            // Handle deletion of existing additional images
            if (isset($_POST['delete_image_ids']) && is_array($_POST['delete_image_ids'])) {
                foreach ($_POST['delete_image_ids'] as $image_id) {
                    $image_id = filter_var($image_id, FILTER_VALIDATE_INT);
                    if ($image_id !== false) {
                        $stmt_fetch_path = $pdo->prepare("SELECT image_path FROM product_images WHERE id = :id AND product_id = :product_id");
                        $stmt_fetch_path->execute(['id' => $image_id, 'product_id' => $id]);
                        $img_to_delete = $stmt_fetch_path->fetch(PDO::FETCH_ASSOC);

                        if ($img_to_delete) {
                            deleteImageFile('../../' . $img_to_delete['image_path']);
                            $stmt_delete_img = $pdo->prepare("DELETE FROM product_images WHERE id = :id AND product_id = :product_id");
                            $stmt_delete_img->execute(['id' => $image_id, 'product_id' => $id]);
                        }
                    }
                }
            }

            $pdo->commit();
            $message = 'Product updated successfully!';
            $message_type = 'success';
            // Re-fetch product and images to display updated data
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt_img = $pdo->prepare("SELECT id, image_path FROM product_images WHERE product_id = :product_id ORDER BY sort_order, id");
            $stmt_img->execute(['product_id' => $id]);
            $additional_images = $stmt_img->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = 'Error updating product: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

if (!$product && !$message) {
    $message = 'No product ID provided or product not found.';
    $message_type = 'danger';
}
?>

<div class="container-fluid">
    <h1 class="mb-4">Edit Product</h1>
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

            <?php if ($product): ?>
                <form method="POST" action="edit_product.php" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Short Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="details" class="form-label">Detailed Description</label>
                        <textarea class="form-control" id="details" name="details" rows="5"><?= htmlspecialchars($product['details']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon (Font Awesome class, e.g., fas fa-laptop) <small class="text-muted">(Optional, for legacy use)</small></label>
                        <input type="text" class="form-control" id="icon" name="icon" value="<?= htmlspecialchars($product['icon']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="main_image" class="form-label">Main Product Image</label>
                        <?php if (!empty($product['main_image'])): ?>
                            <div class="mb-2">
                                <img src="../../<?= htmlspecialchars($product['main_image']) ?>" alt="Main Image" style="max-width: 150px; height: auto; border: 1px solid #ddd; padding: 5px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="main_image" name="main_image" accept="image/*">
                        <small class="form-text text-muted">Upload a new main image to replace the current one.</small>
                    </div>
                    <div class="mb-3">
                        <label for="additional_images" class="form-label">Additional Product Images</label>
                        <div class="row mb-2">
                            <?php if (!empty($additional_images)): ?>
                                <?php foreach ($additional_images as $img): ?>
                                    <div class="col-md-3 col-sm-4 col-6 mb-2 image-item" id="image-<?= $img['id'] ?>">
                                        <img src="../<?= htmlspecialchars($img['image_path']) ?>" class="img-fluid" style="border: 1px solid #ddd; padding: 5px;">
                                        <button type="button" class="btn btn-danger btn-sm mt-1 delete-additional-image" data-image-id="<?= $img['id'] ?>" data-image-path="<?= htmlspecialchars($img['image_path']) ?>">Delete</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No additional images.</p>
                            <?php endif; ?>
                        </div>
                        <input type="file" class="form-control" id="additional_images" name="additional_images[]" accept="image/*" multiple>
                        <small class="form-text text-muted">Upload more images for the product gallery.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="products.php" class="btn btn-secondary">Back to Products</a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-additional-image').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this image?')) {
                const imageId = this.dataset.imageId;
                const imagePath = this.dataset.imagePath;
                
                // Create a hidden input to send the ID for deletion
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'delete_image_ids[]';
                hiddenInput.value = imageId;
                this.form.appendChild(hiddenInput);

                // Visually remove the image item
                document.getElementById(`image-${imageId}`).remove();
            }
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>
