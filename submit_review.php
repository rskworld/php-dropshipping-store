<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT);
    $review = trim($_POST['review'] ?? '');

    if ($product_id && $rating >= 1 && $rating <= 5) {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, review) VALUES (:product_id, :user_id, :rating, :review)");
            $stmt->execute([
                'product_id' => $product_id,
                'user_id' => $_SESSION['user_id'],
                'rating' => $rating,
                'review' => $review
            ]);
        } catch (PDOException $e) {
            // Handle error
        }
    }
}

header('Location: product_detail.php?id=' . $product_id);
exit;
