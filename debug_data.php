<?php
require_once 'db_connect.php';

echo "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Database Debug</title><link href=\"https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css\" rel=\"stylesheet\"></head><body><div class=\"container mt-5\">";
echo "<h1>Database Debug Viewer</h1>";

function display_table($pdo, $tableName) {
    echo "<h2>Contents of `{$tableName}` table:</h2>";
    try {
        $stmt = $pdo->query("SELECT * FROM {$tableName}");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            echo "<div class=\"alert alert-warning\">Table `{$tableName}` is empty.</div>";
        } else {
            echo "<div class=\"table-responsive\"><table class=\"table table-bordered table-striped\">";
            echo "<thead class=\"thead-dark\"><tr>";
            foreach ($results[0] as $key => $value) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr></thead>";
            echo "<tbody>";
            foreach ($results as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table></div>";
        }
    } catch (PDOException $e) {
        echo "<div class=\"alert alert-danger\">Error fetching from `{$tableName}`: " . $e->getMessage() . "</div>";
    }
    echo "<hr>";
}

display_table($pdo, 'orders');
display_table($pdo, 'order_items');
display_table($pdo, 'products');
display_table($pdo, 'categories');
display_table($pdo, 'users');


echo "</div></body></html>";
?>