<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$term = $_GET['term'] ?? '';
$suggestions = [];

if (strlen($term) > 1) {
    try {
        $stmt = $pdo->prepare("SELECT name FROM products WHERE name LIKE :term LIMIT 10");
        $stmt->execute(['term' => "%$term%"]);
        $suggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        // Log error, but don't output to user
        error_log($e->getMessage());
    }
}

echo json_encode($suggestions);
?>