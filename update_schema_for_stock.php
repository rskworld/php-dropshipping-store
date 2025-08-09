<?php
require_once 'db_connect.php';

echo "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Database Update</title><link href=\"https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css\" rel=\"stylesheet\"></head><body><div class=\"container mt-5\"><h1>Database Update Script (Stock Management)</h1>";

$sql = "ALTER TABLE products ADD COLUMN stock_quantity INT NOT NULL DEFAULT 0 AFTER price;";

try {
    $pdo->exec($sql);
    echo "<div class=\"alert alert-success\">Successfully updated the `products` table by adding the `stock_quantity` column.</div>";
} catch (PDOException $e) {
    // Check if the error is because the column already exists
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "<div class=\"alert alert-info\">Column `stock_quantity` already exists in the `products` table. No action needed.</div>";
    } else {
        echo "<div class=\"alert alert-danger\">Error updating table: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

echo "</div></body></html>";
?>