<?php
session_start();
require_once 'db_connect.php';

$pageTitle = 'Subscription | RSK Dropshipping Template';
$currentPage = 'subscription';

$message = '';
$message_type = '';
$current_plan = 'Starter'; // Default plan

// Check if user is logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    $user_id = $_SESSION['user_id'];

    // Handle subscription form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['choose_plan'])) {
        $chosen_plan = $_POST['plan_name'];
        try {
            $stmt = $pdo->prepare("UPDATE users SET subscription_plan = :plan WHERE id = :id");
            $stmt->execute(['plan' => $chosen_plan, 'id' => $user_id]);
            $message = "You have successfully subscribed to the {$chosen_plan} plan!";
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error updating subscription: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }

    // Fetch user's current plan
    try {
        $stmt = $pdo->prepare("SELECT subscription_plan FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch();
        if ($user && !empty($user['subscription_plan'])) {
            $current_plan = $user['subscription_plan'];
        }
    } catch (PDOException $e) {
        // Error fetching plan, use default
    }
}

require 'header.php';

$plans = [
    'Starter' => [
        'price' => '0',
        'features' => [
            'Up to 50 products',
            '1 Sales Channel',
            'Community Support'
        ]
    ],
    'Pro' => [
        'price' => '2499',
        'features' => [
            '1,000 products',
            '3 Sales Channels',
            'Email & Chat Support',
            'Automated Order Fulfillment',
            'Advanced Analytics',
            'Custom Domain',
            '24/7 Customer Support'
        ]
    ],
    'Enterprise' => [
        'price' => 'Custom',
        'features' => [
            'Unlimited products',
            'Unlimited Channels',
            'Dedicated Account Manager',
            'Priority 24/7 Support'
        ]
    ]
];

?>

<div class="container page-content" style="margin-top: 100px;">
    <h1 class="section-title">Simple & Transparent Subscription</h1>
    <p class="text-center mb-5">Choose the plan that fits your business. No hidden fees, cancel anytime.</p>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>" role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4 justify-content-center">
        <?php foreach ($plans as $plan_name => $details): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm <?= ($plan_name === $current_plan) ? 'border-primary shadow-lg' : '' ?>">
                    <div class="card-body text-center p-4 d-flex flex-column">
                        <?php if ($plan_name === $current_plan): ?>
                            <span class="badge bg-primary align-self-center mb-2">Current Plan</span>
                        <?php endif; ?>
                        <h5 class="card-title"><?= $plan_name ?></h5>
                        <h2 class="display-5 fw-bold mb-3">
                            <?= ($details['price'] === 'Custom') ? 'Custom' : '₹' . $details['price'] ?>
                            <?php if ($details['price'] !== 'Custom'): ?>
                                <span class="h6 fw-normal text-muted">/mo</span>
                            <?php endif; ?>
                        </h2>
                        <ul class="list-unstyled text-start mb-4">
                            <?php foreach ($details['features'] as $feature): ?>
                                <li><i class="fas fa-check text-success me-2"></i> <?= $feature ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="mt-auto">
                            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                                <?php if ($plan_name === $current_plan): ?>
                                    <button class="btn btn-secondary w-100" disabled>Currently Active</button>
                                <?php elseif ($plan_name === 'Enterprise'): ?>
                                    <a href="contact.php" class="btn btn-primary w-100">Contact Sales</a>
                                <?php else: ?>
                                    <form method="POST" action="subscription.php">
                                        <input type="hidden" name="plan_name" value="<?= $plan_name ?>">
                                        <input type="hidden" name="plan_price" value="<?= $details['price'] ?>">
                                        <button type="submit" name="choose_plan" class="btn btn-primary w-100">Choose Plan</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login-register.php" class="btn btn-primary w-100">Login to Subscribe</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require 'footer.php'; ?>
