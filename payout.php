<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in'] || !isset($_SESSION['pending_plan'])) {
    header('Location: subscription.php');
    exit;
}

$pageTitle = 'Complete Your Subscription | RSK Dropshipping Template';
$currentPage = 'subscription';

$plan_name = $_SESSION['pending_plan']['name'];
$plan_price = $_SESSION['pending_plan']['price'];

require 'header.php';
?>

<div class="container page-content" style="margin-top: 100px;">
    <h1 class="section-title">Complete Your Purchase</h1>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card feature-card">
                <div class="card-body p-5">
                    <h3 class="mb-4">Order Summary</h3>
                    <div class="d-flex justify-content-between">
                        <h4>Plan: <?= htmlspecialchars($plan_name) ?></h4>
                        <h4>Price: ₹<?= htmlspecialchars($plan_price) ?>/mo</h4>
                    </div>
                    <hr>
                    <h3 class="mb-4">Mock Payment Gateway</h3>
                    <p class="text-muted">This is a simulated payment form. No real payment will be processed.</p>
                    <form action="confirm_subscription.php" method="POST">
                        <div class="mb-3">
                            <label for="card_number" class="form-label">Card Number</label>
                            <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9101 1121" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" id="expiry_date" placeholder="MM/YY" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cvc" class="form-label">CVC</label>
                                <input type="text" class="form-control" id="cvc" placeholder="123" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg">Confirm Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
