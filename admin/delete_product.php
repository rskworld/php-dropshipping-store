<?php
require_once '../db_connect.php';
require_once 'auth.php'; // Ensure admin is logged in

// Define upload directory
$upload_dir = '../../uploads/products/';

// Function to delete image from file system
function deleteImageFile($path) {
    global $upload_dir;
    $full_path = $upload_dir . basename($path); // Ensure we only delete from our intended directory
    if (file_exists($full_path) && is_file($full_path)) {
        unlink($full_path);
    }
}

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($id === false) {
        $_SESSION['admin_message'] = ['type' => 'danger', 'text' => 'Invalid product ID.'];
    } else {
        try {
            $pdo->beginTransaction();

            // Fetch main image path
            $stmt_main_img = $pdo->prepare("SELECT main_image FROM products WHERE id = :id");
            $stmt_main_img->execute(['id' => $id]);
            $main_image = $stmt_main_img->fetch(PDO::FETCH_ASSOC);

            // Fetch additional image paths
            $stmt_add_imgs = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = :id");
            $stmt_add_imgs->execute(['id' => $id]);
            $additional_images = $stmt_add_imgs->fetchAll(PDO::FETCH_ASSOC);

            // Delete product from database (this will cascade delete from product_images)
            $stmt_delete_product = $pdo->prepare("DELETE FROM products WHERE id = :id");
            $stmt_delete_product->execute(['id' => $id]);

            if ($stmt_delete_product->rowCount()) {
                // Delete main image file
                if ($main_image && !empty($main_image['main_image']) && strpos($main_image['main_image'], 'placehold.co') === false) {
                    deleteImageFile($main_image['main_image']);
                }

                // Delete additional image files
                foreach ($additional_images as $img) {
                    deleteImageFile($img['image_path']);
                }

                $pdo->commit();
                $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Product deleted successfully!'];
            } else {
                $pdo->rollBack();
                $_SESSION['admin_message'] = ['type' => 'warning', 'text' => 'Product not found.'];
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['admin_message'] = ['type' => 'danger', 'text' => 'Error deleting product: ' . $e->getMessage()];
        }
    }
} else {
    $_SESSION['admin_message'] = ['type' => 'danger', 'text' => 'No product ID provided for deletion.'];
}

header('Location: products.php');
exit;

