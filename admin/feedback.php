<?php
$adminPageTitle = 'Feedback';
$currentAdminPage = 'feedback';
require_once 'header.php';
require_once '../db_connect.php';

$message = '';
$message_type = '';

// Handle actions (mark as read, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['feedback_id'])) {
        $feedback_id = filter_var($_POST['feedback_id'], FILTER_VALIDATE_INT);
        if ($feedback_id === false) {
            $message = 'Invalid feedback ID.';
            $message_type = 'danger';
        } else {
            try {
                if ($_POST['action'] === 'mark_read') {
                    $stmt = $pdo->prepare("UPDATE feedback SET is_read = 1 WHERE id = :id");
                    $stmt->execute(['id' => $feedback_id]);
                    $message = 'Feedback marked as read.';
                    $message_type = 'success';
                } elseif ($_POST['action'] === 'delete') {
                    $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = :id");
                    $stmt->execute(['id' => $feedback_id]);
                    $message = 'Feedback deleted.';
                    $message_type = 'success';
                }
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    }
}

// Fetch feedback messages
$feedback_messages = [];
try {
    $stmt = $pdo->query("SELECT * FROM feedback ORDER BY created_at DESC");
    $feedback_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Error fetching feedback: ' . $e->getMessage();
    $message_type = 'danger';
}
?>

<div class="container-fluid">
    <h1 class="mb-4">Customer Feedback</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>" role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            Feedback Messages
        </div>
        <div class="card-body">
            <?php if (empty($feedback_messages)): ?>
                <div class="alert alert-info">No feedback messages found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedback_messages as $feedback): ?>
                                <tr class="<?= $feedback['is_read'] ? 'text-muted' : 'fw-bold' ?>">
                                    <td><?= htmlspecialchars($feedback['id']) ?></td>
                                    <td><?= htmlspecialchars($feedback['first_name']) ?> <?= htmlspecialchars($feedback['last_name']) ?></td>
                                    <td><?= htmlspecialchars($feedback['email']) ?></td>
                                    <td><?= htmlspecialchars($feedback['subject']) ?></td>
                                    <td><?= nl2br(htmlspecialchars(substr($feedback['message'], 0, 100))) ?><?= (strlen($feedback['message']) > 100) ? '...' : '' ?></td>
                                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($feedback['created_at']))) ?></td>
                                    <td>
                                        <?= $feedback['is_read'] ? '<span class="badge bg-secondary">Read</span>' : '<span class="badge bg-success">New</span>' ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="feedback.php" class="d-inline">
                                            <input type="hidden" name="feedback_id" value="<?= $feedback['id'] ?>">
                                            <?php if (!$feedback['is_read']): ?>
                                                <button type="submit" name="action" value="mark_read" class="btn btn-sm btn-success me-1">Mark Read</button>
                                            <?php endif; ?>
                                            <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this feedback?');">Delete</button>
                                        </form>
                                    </td>
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
