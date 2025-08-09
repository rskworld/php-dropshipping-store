<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login-register.php');
    exit;
}

$pageTitle = 'My Account | RSK Dropshipping Template';
$currentPage = 'my_account';
$user_id = $_SESSION['user_id'];

$message = '';
$message_type = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    if (!empty($name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
            $stmt->execute(['name' => $name, 'email' => $email, 'id' => $user_id]);
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $message = 'Profile updated successfully!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error updating profile: ' . $e->getMessage();
            $message_type = 'danger';
        }
    } else {
        $message = 'Invalid name or email.';
        $message_type = 'danger';
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($current_password, $user['password_hash'])) {
        if ($new_password === $confirm_password) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :id");
            $stmt->execute(['password_hash' => $password_hash, 'id' => $user_id]);
            $message = 'Password changed successfully!';
            $message_type = 'success';
        } else {
            $message = 'New passwords do not match.';
            $message_type = 'danger';
        }
    } else {
        $message = 'Incorrect current password.';
        $message_type = 'danger';
    }
}

// Handle Add/Edit Address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_address'])) {
    $address_id = $_POST['address_id'] ?? null;
    $address = trim($_POST['address']);
    $pincode = trim($_POST['pincode']);
    $landmark = trim($_POST['landmark']);

    if ($address_id) { // Update
        $stmt = $pdo->prepare("UPDATE user_addresses SET address = :address, pincode = :pincode, landmark = :landmark WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['address' => $address, 'pincode' => $pincode, 'landmark' => $landmark, 'id' => $address_id, 'user_id' => $user_id]);
        $message = 'Address updated successfully!';
    } else { // Insert
        $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, address, pincode, landmark) VALUES (:user_id, :address, :pincode, :landmark)");
        $stmt->execute(['user_id' => $user_id, 'address' => $address, 'pincode' => $pincode, 'landmark' => $landmark]);
        $message = 'Address added successfully!';
    }
    $message_type = 'success';
}

// Handle Delete Address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_address'])) {
    $address_id = $_POST['address_id'];
    $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $address_id, 'user_id' => $user_id]);
    $message = 'Address deleted successfully!';
    $message_type = 'success';
}


// Fetch user's orders
$orders = [];
try {
    $stmt = $pdo->prepare("SELECT o.*, oi.product_name, oi.quantity, oi.price_per_unit FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE o.user_id = :user_id ORDER BY o.created_at DESC, o.id DESC");
    $stmt->execute(['user_id' => $user_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($order_items as $item) {
        $order_id = $item['id'];
        if (!isset($orders[$order_id])) {
            $orders[$order_id] = [
                'id' => $item['id'],
                'created_at' => $item['created_at'],
                'total_amount' => $item['total_amount'],
                'order_status' => $item['order_status'],
                'items' => []
            ];
        }
        $orders[$order_id]['items'][] = $item;
    }
} catch (PDOException $e) {
    $message = 'Error fetching orders: ' . $e->getMessage();
    $message_type = 'danger';
}

// Fetch user's addresses
$addresses = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Error fetching addresses: ' . $e->getMessage();
    $message_type = 'danger';
}


require 'header.php';
?>

<div class="container page-content" style="margin-top: 100px;">
    <h1 class="section-title">My Account</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>" role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs" id="myAccountTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="true">Order History</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="addresses-tab" data-bs-toggle="tab" data-bs-target="#addresses" type="button" role="tab" aria-controls="addresses" aria-selected="false">Manage Addresses</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Profile Settings</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab" aria-controls="password" aria-selected="false">Change Password</button>
        </li>
    </ul>

    <div class="tab-content" id="myAccountTabContent">
        <!-- Order History Tab -->
        <div class="tab-pane fade show active" id="orders" role="tabpanel" aria-labelledby="orders-tab">
            <div class="accordion mt-3" id="ordersAccordion">
                <?php if (empty($orders)): ?>
                    <p class="mt-3">You have not placed any orders yet.</p>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $order['id'] ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $order['id'] ?>" aria-expanded="false" aria-controls="collapse<?= $order['id'] ?>">
                                    Order #<?= htmlspecialchars($order['id']) ?> - <?= htmlspecialchars(date('F j, Y', strtotime($order['created_at']))) ?>
                                    <span class="badge bg-info ms-3">Total: ₹<?= number_format($order['total_amount'], 2) ?></span>
                                    <span class="badge bg-secondary ms-2">Status: <?= htmlspecialchars($order['order_status']) ?></span>
                                </button>
                            </h2>
                            <div id="collapse<?= $order['id'] ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $order['id'] ?>" data-bs-parent="#ordersAccordion">
                                <div class="accordion-body">
                                    <h5>Order Details</h5>
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Price/Unit</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($order['items'] as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                                                    <td>₹<?= number_format($item['price_per_unit'], 2) ?></td>
                                                    <td>₹<?= number_format($item['quantity'] * $item['price_per_unit'], 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Manage Addresses Tab -->
        <div class="tab-pane fade" id="addresses" role="tabpanel" aria-labelledby="addresses-tab">
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Your Addresses</h5>
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addressModal">Add New Address</button>
                    <div class="row">
                        <?php foreach ($addresses as $address): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <p><?= nl2br(htmlspecialchars($address['address'])) ?></p>
                                        <p><?= htmlspecialchars($address['pincode']) ?></p>
                                        <p><?= htmlspecialchars($address['landmark']) ?></p>
                                        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#addressModal" data-address-id="<?= $address['id'] ?>" data-address="<?= htmlspecialchars($address['address']) ?>" data-pincode="<?= htmlspecialchars($address['pincode']) ?>" data-landmark="<?= htmlspecialchars($address['landmark']) ?>">Edit</button>
                                        <form method="POST" action="my_account.php" class="d-inline">
                                            <input type="hidden" name="address_id" value="<?= $address['id'] ?>">
                                            <button type="submit" name="delete_address" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Settings Tab -->
        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Update Your Profile</h5>
                    <form method="POST" action="my_account.php">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($_SESSION['user_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_SESSION['user_email']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password Tab -->
        <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Change Your Password</h5>
                    <form method="POST" action="my_account.php">
                        <input type="hidden" name="change_password" value="1">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addressModalLabel">Add/Edit Address</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addressForm" method="POST" action="my_account.php">
            <input type="hidden" name="save_address" value="1">
            <input type="hidden" id="address_id" name="address_id">
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="modal_address" name="address" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="pincode" class="form-label">Pincode</label>
                <input type="text" class="form-control" id="modal_pincode" name="pincode" required>
            </div>
            <div class="mb-3">
                <label for="landmark" class="form-label">Landmark</label>
                <input type="text" class="form-control" id="modal_landmark" name="landmark">
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" form="addressForm" class="btn btn-primary">Save Address</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var addressModal = document.getElementById('addressModal');
    addressModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var addressId = button.getAttribute('data-address-id');
        var address = button.getAttribute('data-address');
        var pincode = button.getAttribute('data-pincode');
        var landmark = button.getAttribute('data-landmark');

        var modalTitle = addressModal.querySelector('.modal-title');
        var addressIdInput = addressModal.querySelector('#address_id');
        var addressInput = addressModal.querySelector('#modal_address');
        var pincodeInput = addressModal.querySelector('#modal_pincode');
        var landmarkInput = addressModal.querySelector('#modal_landmark');

        if (addressId) {
            modalTitle.textContent = 'Edit Address';
            addressIdInput.value = addressId;
            addressInput.value = address;
            pincodeInput.value = pincode;
            landmarkInput.value = landmark;
        } else {
            modalTitle.textContent = 'Add New Address';
            addressIdInput.value = '';
            addressInput.value = '';
            pincodeInput.value = '';
            landmarkInput.value = '';
        }
    });
});
</script>

<?php require 'footer.php'; ?>