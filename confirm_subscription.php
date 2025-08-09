<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in'] || !isset($_SESSION['pending_plan'])) {
    header('Location: subscription.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$chosen_plan = $_SESSION['pending_plan']['name'];

try {
    $stmt = $pdo->prepare("UPDATE users SET subscription_plan = :plan WHERE id = :id");
    $stmt->execute(['plan' => $chosen_plan, 'id' => $user_id]);
    
    // Set success message and clear pending plan from session
    $_SESSION['subscription_message'] = ['type' => 'success', 'text' => "You have successfully subscribed to the {$chosen_plan} plan!"];
    unset($_SESSION['pending_plan']);

} catch (PDOException $e) {
    $_SESSION['subscription_message'] = ['type' => 'danger', 'text' => 'Error updating subscription: ' . $e->getMessage()];
}

header('Location: subscription.php');
exit;
