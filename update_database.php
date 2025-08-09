<?php
require_once 'db_connect.php';

echo "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Database Update</title><link href=\"https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css\" rel=\"stylesheet\"></head><body><div class=\"container mt-5\"><h1>Database Update Script</h1>";

$sql = "ALTER TABLE orders ADD COLUMN pincode VARCHAR(10) NULL AFTER customer_address, ADD COLUMN landmark VARCHAR(255) NULL AFTER pincode;";

try {
    $pdo->exec($sql);
    echo "<div class=\"alert alert-success\">Successfully updated the `orders` table by adding `pincode` and `landmark` columns.</div>";
} catch (PDOException $e) {
    // Check if the error is because columns already exist
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "<div class=\"alert alert-info\">Columns `pincode` and `landmark` already exist in the `orders` table. No action needed.</div>";
    } else {
        echo "<div class=\"alert alert-danger\">Error updating table: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

echo "</div></body></html>";
?>