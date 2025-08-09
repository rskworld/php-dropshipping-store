<?php
session_start();
require_once 'config.php';
require_once 'db_connect.php';

$pageTitle = SITE_NAME . ' | Product Details';
$currentPage = 'products';

$product = null;
$additional_images = [];

if (isset($_GET['id'])) {
    $product_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($product_id === false) {
        // Invalid ID, redirect or show error
        header('Location: products.php');
        exit;
    }

    try {
        // Fetch product details
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            // Product not found, redirect or show error
            header('Location: products.php');
            exit;
        }

        // Fetch additional images
        $stmt_img = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = :product_id ORDER BY sort_order, id");
        $stmt_img->execute(['product_id' => $product_id]);
        $additional_images = $stmt_img->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // Database error
        error_log("Error fetching product details: " . $e->getMessage());
        header('Location: products.php');
        exit;
    }
} else {
    // No ID provided, redirect
    header('Location: products.php');
    exit;
}

require 'header.php';

$reviews = [];
$average_rating = 0;

try {
    // Fetch reviews
    $stmt_reviews = $pdo->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = :product_id ORDER BY r.created_at DESC");
    $stmt_reviews->execute(['product_id' => $product_id]);
    $reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);

    // Calculate average rating
    if (count($reviews) > 0) {
        $total_rating = 0;
        foreach ($reviews as $review) {
            $total_rating += $review['rating'];
        }
        $average_rating = $total_rating / count($reviews);
    }
} catch (PDOException $e) {
    // Handle error
}

    // Handle error

$wishlist_product_ids = [];
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    try {
        $stmt_wishlist = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = :user_id");
        $stmt_wishlist->execute(['user_id' => $_SESSION['user_id']]);
        $wishlist_product_ids = $stmt_wishlist->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        // Handle error
    }
}

$in_wishlist = in_array($product_id, $wishlist_product_ids);

$related_products = [];
try {
    if ($product['category_id']) {
        $stmt_related = $pdo->prepare("SELECT * FROM products WHERE category_id = :category_id AND id != :product_id LIMIT 4");
        $stmt_related->execute(['category_id' => $product['category_id'], 'product_id' => $product_id]);
        $related_products = $stmt_related->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Handle error
}

?>

<div class="container page-content" style="margin-top: 100px; padding-bottom: 120px;">
    <div class="row">
        <div class="col-lg-6">
            <div id="productImageCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="<?= htmlspecialchars($product['main_image']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($product['name']) ?> Main Image">
                    </div>
                    <?php foreach ($additional_images as $index => $image): ?>
                        <div class="carousel-item">
                            <img src="<?= htmlspecialchars($image['image_path']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($product['name']) ?> Image <?= $index + 1 ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($additional_images) > 0): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productImageCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productImageCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                <?php endif; ?>
            </div>
            <div class="d-flex justify-content-center mt-3">
                <img src="<?= htmlspecialchars($product['main_image']) ?>" class="img-thumbnail me-2" style="width: 80px; cursor: pointer;" data-bs-target="#productImageCarousel" data-bs-slide-to="0" class="active" aria-current="true" alt="Main thumbnail">
                <?php foreach ($additional_images as $index => $image): ?>
                    <img src="<?= htmlspecialchars($image['image_path']) ?>" class="img-thumbnail me-2" style="width: 80px; cursor: pointer;" data-bs-target="#productImageCarousel" data-bs-slide-to="<?= $index + 1 ?>" alt="Additional thumbnail <?= $index + 1 ?>">
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-lg-6">
            <h1 class="mb-3"><?= htmlspecialchars($product['name']) ?></h1>
            <div class="d-flex align-items-center mb-3">
                <div class="me-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?= ($i <= $average_rating) ? 'text-warning' : 'text-muted' ?>"></i>
                    <?php endfor; ?>
                </div>
                <span class="text-muted">(<?= count($reviews) ?> reviews)</span>
            </div>
            <p class="lead text-primary fs-3">₹<?= number_format($product['price'], 2) ?></p>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <hr>
            <h4>Product Details</h4>
            <p><?= nl2br(htmlspecialchars($product['details'])) ?></p>
            
            <button class="btn btn-primary btn-lg add-to-cart" data-product-id="<?= htmlspecialchars($product['id']) ?>" data-product-name="<?= htmlspecialchars($product['name']) ?>">
                <i class="fas fa-shopping-cart me-2"></i> Add to Cart
            </button>
            <button class="btn btn-outline-danger btn-lg toggle-wishlist" data-product-id="<?= $product['id'] ?>">
                <i class="<?= $in_wishlist ? 'fas' : 'far' ?> fa-heart"></i>
            </button>
        </div>
    </div>

    <hr class="my-5">

    <div class="row">
        <div class="col-lg-8">
            <h2 class="mb-4">Customer Reviews</h2>
            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Write a Review</h5>
                        <form action="submit_review.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $product_id ?>">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <select class="form-select" id="rating" name="rating" required>
                                    <option value="5">5 Stars (Excellent)</option>
                                    <option value="4">4 Stars (Good)</option>
                                    <option value="3">3 Stars (Average)</option>
                                    <option value="2">2 Stars (Fair)</option>
                                    <option value="1">1 Star (Poor)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="review" class="form-label">Review</label>
                                <textarea class="form-control" id="review" name="review" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <p><a href="login-register.php">Log in</a> to write a review.</p>
            <?php endif; ?>

            <?php if (empty($reviews)): ?>
                <p>No reviews yet. Be the first to review this product!</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($review['user_name']) ?></h5>
                            <div class="mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= ($i <= $review['rating']) ? 'text-warning' : 'text-muted' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="card-text"><?= nl2br(htmlspecialchars($review['review'])) ?></p>
                            <small class="text-muted">Reviewed on <?= date('F j, Y', strtotime($review['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <hr class="my-5">

    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Related Products</h2>
            <div class="row">
                <?php if (empty($related_products)): ?>
                    <p>No related products found.</p>
                <?php else: ?>
                    <?php foreach ($related_products as $p): ?>
                        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                            <div class="product-card h-100">
                                <a href="product_detail.php?id=<?= htmlspecialchars($p['id']) ?>" class="text-decoration-none text-dark">
                                    <div class="product-image">
                                        <img src="<?= htmlspecialchars($p['main_image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="img-fluid">
                                        <button class="btn btn-sm btn-outline-danger toggle-wishlist wishlist-icon-btn" data-product-id="<?= $p['id'] ?>">
                                            <i class="<?= in_array($p['id'], $wishlist_product_ids) ? 'fas' : 'far' ?> fa-heart"></i>
                                        </button>
                                    </div>
                                    <div class="p-3">
                                        <h5><?= htmlspecialchars($p['name']) ?></h5>
                                        <p class="text-muted small mb-3"><?= htmlspecialchars($p['description']) ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 text-primary mb-0">₹<?= number_format($p['price'], 2) ?></span>
                                        </div>
                                    </div>
                                </a>
                                <div class="p-3 pt-0">
                                    <button class="btn btn-sm btn-primary add-to-cart w-100" data-product-id="<?= htmlspecialchars($p['id']) ?>" data-product-name="<?= htmlspecialchars($p['name']) ?>">Add to Cart</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>

<!-- Toast container for notifications -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="add-to-cart-toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <!-- Message will be inserted here -->
        </div>
    </div>
</div>



