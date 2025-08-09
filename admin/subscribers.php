<?php
$adminPageTitle = 'Subscribers';
$currentAdminPage = 'subscribers';
require_once 'header.php';
require_once '../db_connect.php';

$subscribers = [];
try {
    $stmt = $pdo->query("SELECT * FROM subscribers ORDER BY subscribed_at DESC");
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class=\"alert alert-danger\">Error: " . $e->getMessage() . "</div>";
}
?>

<div class="container-fluid">
    <h1 class="mb-4">Newsletter Subscribers</h1>
    <div class="card mb-4">
        <div class="card-header">Subscriber List</div>
        <div class="card-body">
            <?php if (empty($subscribers)): ?>
                <div class="alert alert-info">No subscribers found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Subscription Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscribers as $subscriber): ?>
                                <tr>
                                    <td><?= htmlspecialchars($subscriber['id']) ?></td>
                                    <td><?= htmlspecialchars($subscriber['email']) ?></td>
                                    <td><?= htmlspecialchars($subscriber['subscribed_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
