<?php
// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'dropstep'); // Replace with your database name
define('DB_USER', 'root');     // Replace with your database username
define('DB_PASS', '');     // Replace with your database password

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
