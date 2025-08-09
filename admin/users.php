<?php
$adminPageTitle = 'Users';
$currentAdminPage = 'users';
require_once 'header.php';
require_once '../db_connect.php';

$search_query = $_GET['search'] ?? '';

// Fetch users
$users = [];
try {
    $sql = "SELECT id, name, email, subscription_plan, created_at FROM users";
    $params = [];
    if (!empty($search_query)) {
        $sql .= " WHERE name LIKE :search OR email LIKE :search";
        $params[':search'] = "%$search_query%";
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class=\"alert alert-danger\">Error: " . $e->getMessage() . "</div>";
}
?>

<div class="container-fluid">
    <h1 class="mb-4">User Management</h1>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            User List
            <form action="users.php" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search by name or email..." value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
        <div class="card-body">
            <?php if (empty($users)): ?>
                <div class="alert alert-info">No users found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subscription Plan</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['id']) ?></td>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><span class="badge bg-success"><?= htmlspecialchars($user['subscription_plan']) ?></span></td>
                                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($user['created_at']))) ?></td>
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
