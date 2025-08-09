<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if ($email) {
        try {
            $stmt = $pdo->prepare("INSERT INTO subscribers (email) VALUES (:email)");
            $stmt->execute(['email' => $email]);
            $_SESSION['subscribe_message'] = 'Thank you for subscribing!';
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                $_SESSION['subscribe_message'] = 'This email is already subscribed.';
            } else {
                $_SESSION['subscribe_message'] = 'An error occurred. Please try again.';
            }
        }
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;
