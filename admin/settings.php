<?php
$adminPageTitle = 'Settings';
$currentAdminPage = 'settings';
require_once 'header.php';
require_once '../db_connect.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings_to_update = [
        'site_name' => $_POST['site_name'] ?? '',
        'contact_email' => $_POST['contact_email'] ?? '',
        'contact_phone' => $_POST['contact_phone'] ?? '',
        'contact_address' => $_POST['contact_address'] ?? '',
        'shipping_charge' => $_POST['shipping_charge'] ?? '',
        'gst_rate' => $_POST['gst_rate'] ?? ''
    ];

    try {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = :key");
        foreach ($settings_to_update as $key => $value) {
            $stmt->execute(['value' => $value, 'key' => $key]);
        }
        $message = 'Settings updated successfully!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Error updating settings: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Fetch current settings
$settings = [];
try {
    $stmt = $pdo->query("SELECT * FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $message = 'Error fetching settings: ' . $e->getMessage();
    $message_type = 'danger';
}
?>

<div class="container-fluid">
    <h1 class="mb-4">Site Settings</h1>
    <div class="card">
        <div class="card-header">
            General Settings
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="settings.php">
                <div class="mb-3">
                    <label for="site_name" class="form-label">Site Name</label>
                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="contact_email" class="form-label">Contact Email</label>
                    <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="contact_phone" class="form-label">Contact Phone</label>
                    <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="contact_address" class="form-label">Contact Address</label>
                    <textarea class="form-control" id="contact_address" name="contact_address" rows="3"><?= htmlspecialchars($settings['contact_address'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="shipping_charge" class="form-label">Shipping Charge (₹)</label>
                    <input type="number" step="0.01" class="form-control" id="shipping_charge" name="shipping_charge" value="<?= htmlspecialchars($settings['shipping_charge'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="gst_rate" class="form-label">GST Rate (%)</label>
                    <input type="number" step="0.01" class="form-control" id="gst_rate" name="gst_rate" value="<?= htmlspecialchars($settings['gst_rate'] * 100) ?>">
                    <small class="form-text text-muted">Enter the GST rate as a percentage (e.g., 18 for 18%).</small>
                </div>
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
