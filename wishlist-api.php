<?php
session_start();
require_once 'db_connect.php';

$response = ['success' => false, 'message' => 'Invalid request.'];

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    $response['message'] = 'You must be logged in to manage your wishlist.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if ($product_id === false) {
        $response['message'] = 'Invalid product ID.';
    } else {
        try {
            // Check if the item is already in the wishlist
            $stmt_check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = :user_id AND product_id = :product_id");
            $stmt_check->execute(['user_id' => $user_id, 'product_id' => $product_id]);
            $existing_item = $stmt_check->fetch();

            if ($existing_item) {
                // Item is in wishlist, so remove it
                $stmt_delete = $pdo->prepare("DELETE FROM wishlist WHERE id = :id");
                $stmt_delete->execute(['id' => $existing_item['id']]);
                $response['success'] = true;
                $response['action'] = 'removed';
                $response['message'] = 'Product removed from wishlist.';

                // Update session
                if (($key = array_search($product_id, $_SESSION['wishlist'])) !== false) {
                    unset($_SESSION['wishlist'][$key]);
                }

            } else {
                // Item is not in wishlist, so add it
                $stmt_insert = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (:user_id, :product_id)");
                $stmt_insert->execute(['user_id' => $user_id, 'product_id' => $product_id]);
                $response['success'] = true;
                $response['action'] = 'added';
                $response['message'] = 'Product added to wishlist.';

                // Update session
                $_SESSION['wishlist'][] = $product_id;
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
