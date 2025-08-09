<?php
$adminPageTitle = 'Account Settings';
$currentAdminPage = 'account';
require_once 'header.php';
require_once '../db_connect.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = 1; // Assuming admin has ID 1, adjust if necessary
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($username)) {
        $message = 'Username cannot be empty.';
        $message_type = 'danger';
    } elseif (!empty($password) && $password !== $password_confirm) {
        $message = 'Passwords do not match.';
        $message_type = 'danger';
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = :username AND id != :id");
            $stmt->execute(['username' => $username, 'id' => $admin_id]);
            if ($stmt->fetch()) {
                $message = 'Username already taken.';
                $message_type = 'danger';
            } else {
                if (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE admin_users SET username = :username, password_hash = :password_hash WHERE id = :id");
                    $stmt->execute([
                        'username' => $username,
                        'password_hash' => $password_hash,
                        'id' => $admin_id
                    ]);
                } else {
                    $stmt = $pdo->prepare("UPDATE admin_users SET username = :username WHERE id = :id");
                    $stmt->execute([
                        'username' => $username,
                        'id' => $admin_id
                    ]);
                }
                $_SESSION['admin_username'] = $username;
                $message = 'Account updated successfully!';
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Error updating account: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Fetch current admin username
$admin_user = null;
try {
    $stmt = $pdo->prepare("SELECT username FROM admin_users WHERE id = 1"); // Assuming admin ID 1
    $stmt->execute();
    $admin_user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Error fetching user data: ' . $e->getMessage();
    $message_type = 'danger';
}
?>

<div class="container-fluid">
    <h1 class="mb-4">Account Settings</h1>
    <div class="card">
        <div class="card-header">
            Update Your Credentials
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="account.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($admin_user['username'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <small class="form-text text-muted">Leave blank to keep the current password.</small>
                </div>
                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                </div>
                <button type="submit" class="btn btn-primary">Update Account</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
