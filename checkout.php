<?php
session_start();

// Ensure user_id is null if not logged in, to prevent foreign key constraint issues
$user_id_to_insert = (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] && !empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id']) && $_SESSION['user_id'] > 0)
    ? $_SESSION['user_id']
    : null;

require_once 'config.php'; // Include API to access constants
require_once 'db_connect.php';

$pageTitle = 'Checkout | RSK Dropshipping Template';
$currentPage = 'checkout';

$order_placed_success = false;
$order_error_message = '';

// Handle removing an item or clearing the cart
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header('Location: checkout.php');
    exit;
}
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header('Location: checkout.php');
    exit;
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_address = trim($_POST['customer_address'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $landmark = trim($_POST['landmark'] ?? '');

    // Safely determine the user ID to insert
    $user_id_to_insert = null; // Default to null for guests
    if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true && !empty($_SESSION['user_id'])) {
        // If logged in, verify the user_id exists in the database to prevent foreign key errors
        $stmt_check_user = $pdo->prepare("SELECT id FROM users WHERE id = :id");
        $stmt_check_user->execute(['id' => $_SESSION['user_id']]);
        if ($stmt_check_user->fetch()) {
            // User exists, use the ID
            $user_id_to_insert = $_SESSION['user_id'];
        }
        // If user does not exist in DB, $user_id_to_insert remains null.
    }

    if (empty($customer_name) || empty($customer_email) || empty($customer_address) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $order_error_message = 'Please fill in all customer details correctly.';
    } elseif (empty($_SESSION['cart'])) {
        $order_error_message = 'Your cart is empty. Please add products before placing an order.';
    } else {
        try {
            $pdo->beginTransaction();

            // Recalculate totals to ensure accuracy
            $subtotal = 0;
            foreach ($_SESSION['cart'] as $item) {
                $subtotal += $item['price'] * $item['qty'];
            }
            $shipping = empty($_SESSION['cart']) ? 0 : SHIPPING_CHARGE;
            $gst = $subtotal * GST_RATE;
            $grandTotal = $subtotal + $shipping + $gst;

            // Insert into orders table
            $stmt_order = $pdo->prepare("INSERT INTO orders (user_id, customer_name, customer_email, customer_address, pincode, landmark, total_amount, shipping_charge, gst_amount, payment_method, order_status) VALUES (:user_id, :name, :email, :address, :pincode, :landmark, :total, :shipping, :gst, :payment_method, 'Pending')");
            $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Cash'; // or any default
            $stmt_order->execute([
                'user_id' => $user_id_to_insert,
                'name' => $customer_name,
                'email' => $customer_email,
                'address' => $customer_address,
                'pincode' => $pincode,
                'landmark' => $landmark,
                'total' => $grandTotal,
                'shipping' => $shipping,
                'gst' => $gst,
                'payment_method' => $payment_method
            ]);
            $order_id = $pdo->lastInsertId();

            // Insert into order_items table
            $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price_per_unit) VALUES (:order_id, :product_id, :product_name, :quantity, :price_per_unit)");
            foreach ($_SESSION['cart'] as $item) {
                $stmt_item->execute([
                    'order_id' => $order_id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'price_per_unit' => $item['price']
                ]);
            }

            $pdo->commit();
            $_SESSION['cart'] = []; // Clear cart after successful order
            $order_placed_success = true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $order_error_message = 'Error placing order: ' . $e->getMessage();
        }
    }
}

require 'header.php';

// Recalculate totals for initial page load or after an action
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['qty'];
}
$shipping = empty($_SESSION['cart']) ? 0 : SHIPPING_CHARGE;
$gst = $subtotal * GST_RATE;
$grandTotal = $subtotal + $shipping + $gst;
?>

<div class="container page-content" style="margin-top: 100px; padding-bottom: 120px;">
    <h1 class="section-title">Your Cart</h1>

    <?php if ($order_placed_success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Your order has been placed successfully! Thank you for your purchase.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif ($order_error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($order_error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-info">Your cart is empty. <a href="products.php">Browse products</a>.</div>
    <?php else: ?>
        <div class="table-responsive mb-4">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col" class="text-end">Price</th>
                        <th scope="col" class="text-center">Qty</th>
                        <th scope="col" class="text-end">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $productId => $item): ?>
                        <tr id="row-<?= htmlspecialchars($productId) ?>">
                            <td>
                                <td>
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 50px; height: 50px; margin-right: 10px; vertical-align: middle;"> <?= htmlspecialchars($item['name']) ?>
                            </td>
                            </td>
                            <td class="text-end">₹<?= number_format($item['price'], 2) ?></td>
                            <td class="text-center">
                                <div class="input-group justify-content-center">
                                    <button class="btn btn-outline-secondary btn-sm quantity-change" data-product-id="<?= htmlspecialchars($productId) ?>" data-action="decrease">-</button>
                                    <span class="form-control text-center quantity-text" style="max-width: 60px;"><?= $item['qty'] ?></span>
                                    <button class="btn btn-outline-secondary btn-sm quantity-change" data-product-id="<?= htmlspecialchars($productId) ?>" data-action="increase">+</button>
                                </div>
                            </td>
                            <td class="text-end subtotal">₹<?= number_format($item['price'] * $item['qty'], 2) ?></td>
                            <td class="text-end">
                                <a href="checkout.php?remove=<?= urlencode($productId) ?>" class="btn btn-sm btn-outline-danger">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Subtotal:</th>
                        <th class="text-end subtotal-amount">₹<?= number_format($subtotal, 2) ?></th>
                        <th></th>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-end">Shipping:</th>
                        <th class="text-end shipping-amount">₹<?= number_format($shipping, 2) ?></th>
                        <th></th>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-end">GST (18%):</th>
                        <th class="text-end gst-amount">₹<?= number_format($gst, 2) ?></th>
                        <th></th>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-end">Grand Total:</th>
                        <th class="text-end total-amount">₹<?= number_format($grandTotal, 2) ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <h2 class="section-title mt-5">Customer Information</h2>
        <form method="POST" action="checkout.php">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="customer_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : '' ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="customer_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="customer_email" name="customer_email" value="<?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '' ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="customer_address" class="form-label">Shipping Address <span class="text-danger">*</span></label>
                <textarea class="form-control" id="customer_address" name="customer_address" rows="3" required></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="pincode" name="pincode" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="landmark" class="form-label">Landmark</label>
                    <input type="text" class="form-control" id="landmark" name="landmark">
                </div>
            </div>
            <div class="mb-3">
                <button type="button" class="btn btn-secondary" id="use-location-btn"><i class="fas fa-map-marker-alt"></i> Use My Location</button>
            </div>
            <h2 class="section-title mt-5">Payment Method</h2>
            <div class="card feature-card p-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="cod" value="Cash on Delivery" required>
                    <label class="form-check-label" for="cod">
                        Cash on Delivery
                    </label>
                </div>
                <hr>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="Credit Card" required>
                    <label class="form-check-label" for="credit_card">
                        Credit Card / Debit Card / UPI
                    </label>
                </div>
            </div>
            <div class="d-flex mt-4">
                <a href="products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                <div class="ms-auto">
                    <a href="checkout.php?clear=1" class="btn btn-outline-danger me-2">Clear Cart</a>
                    <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const useLocationBtn = document.getElementById('use-location-btn');
    if (useLocationBtn) {
        useLocationBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.address) {
                                document.getElementById('customer_address').value = data.display_name;
                                if (data.address.postcode) {
                                    document.getElementById('pincode').value = data.address.postcode;
                                }
                            }
                        });
                }, function() {
                    alert('Unable to retrieve your location.');
                });
            } else {
                alert('Geolocation is not supported by your browser.');
            }
        });
    }
});
</script>
