<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login-register.php');
    exit;
}

$pageTitle = 'My Wishlist | RSK Dropshipping Template';
$currentPage = 'my_account';

$wishlist_items = [];
try {
    $stmt = $pdo->prepare("SELECT p.id, p.name, p.price, p.main_image FROM products p JOIN wishlist w ON p.id = w.product_id WHERE w.user_id = :user_id");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}

require 'header.php';
?>

<div class="container page-content" style="margin-top: 100px;">
    <h1 class="section-title">My Wishlist</h1>

    <?php if (empty($wishlist_items)):
        echo "<p>Your wishlist is empty.</p>";
    else:
    ?>
        <div class="row">
            <?php foreach ($wishlist_items as $item): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="product-card h-100">
                        <a href="product_detail.php?id=<?= htmlspecialchars($item['id']) ?>" class="text-decoration-none text-dark">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($item['main_image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="img-fluid">
                                <button class="btn btn-sm btn-outline-danger toggle-wishlist wishlist-icon-btn" data-product-id="<?= $item['id'] ?>">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                            <div class="p-3">
                                <h5><?= htmlspecialchars($item['name']) ?></h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 text-primary mb-0">₹<?= number_format($item['price'], 2) ?></span>
                                </div>
                            </div>
                        </a>
                        <div class="p-3 pt-0">
                            <button class="btn btn-sm btn-primary add-to-cart w-100" data-product-id="<?= htmlspecialchars($item['id']) ?>" data-product-name="<?= htmlspecialchars($item['name']) ?>">Add to Cart</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>


