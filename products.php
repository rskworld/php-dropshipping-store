<?php
$pageTitle = 'Products | RSK Dropshipping Template';
$currentPage = 'products';
require 'header.php';
require_once 'db_connect.php'; // Include database connection

$products = [];$categories = [];$search_query = trim($_GET['search'] ?? '');$category_filter = trim($_GET['category'] ?? '');$min_price = filter_var($_GET['min_price'] ?? '', FILTER_VALIDATE_FLOAT);$max_price = filter_var($_GET['max_price'] ?? '', FILTER_VALIDATE_FLOAT);$sort_by = $_GET['sort_by'] ?? 'id_desc'; // Default sort

try {
    // Fetch categories
    $stmt_cat = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT p.id, p.name, p.description, p.price, p.main_image FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
    $params = [];

    if (!empty($search_query)) {
        $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
        $params['search'] = "%$search_query%";
    }

    if (!empty($category_filter)) {
        $sql .= " AND c.name = :category";
        $params['category'] = $category_filter;
    }

    if ($min_price !== false && $min_price >= 0) {
        $sql .= " AND p.price >= :min_price";
        $params['min_price'] = $min_price;
    }

    if ($max_price !== false && $max_price >= 0) {
        $sql .= " AND p.price <= :max_price";
        $params['max_price'] = $max_price;
    }

    // Add sorting
    switch ($sort_by) {
        case 'price_asc':
            $sql .= " ORDER BY p.price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY p.price DESC";
            break;
        case 'name_asc':
            $sql .= " ORDER BY p.name ASC";
            break;
        case 'name_desc':
            $sql .= " ORDER BY p.name DESC";
            break;
        default:
            $sql .= " ORDER BY p.id DESC";
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching products for products.php: " . $e->getMessage());
    // Optionally display a user-friendly error message
    echo "<div class=\"alert alert-danger\">Could not load products. Please try again later.<\/div>";
}

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
?>

<div class="container page-content" style="margin-top: 100px; padding-bottom: 120px;">
    <h1 class="section-title">Our Products</h1>
    <div class="row">
        <div class="col-md-3">
            <h4>Categories</h4>
            <div class="list-group">
                <a href="products.php?<?= http_build_query(array_merge($_GET, ['category' => '', 'page' => 1])) ?>" class="list-group-item list-group-item-action <?= empty($category_filter) ? 'active' : '' ?>">All Products</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="products.php?<?= http_build_query(array_merge($_GET, ['category' => $cat['name'], 'page' => 1])) ?>" class="list-group-item list-group-item-action <?= ($category_filter === $cat['name']) ? 'active' : '' ?>"><?= htmlspecialchars($cat['name']) ?></a>
                <?php endforeach; ?>
            </div>

            <h4>Filter by Price</h4>
            <form action="products.php" method="GET" class="mb-4">
                <?php foreach ($_GET as $key => $value): if ($key != 'min_price' && $key != 'max_price') : ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endif; endforeach; ?>
                <div class="mb-2">
                    <label for="min_price" class="form-label">Min Price</label>
                    <input type="number" class="form-control" id="min_price" name="min_price" value="<?= htmlspecialchars($min_price ?? '') ?>" step="0.01">
                </div>
                <div class="mb-3">
                    <label for="max_price" class="form-label">Max Price</label>
                    <input type="number" class="form-control" id="max_price" name="max_price" value="<?= htmlspecialchars($max_price ?? '') ?>" step="0.01">
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100">Apply Filter</button>
            </form>

            <h4>Sort By</h4>
            <div class="list-group">
                <a href="products.php?<?= http_build_query(array_merge($_GET, ['sort_by' => 'id_desc', 'page' => 1])) ?>" class="list-group-item list-group-item-action <?= ($sort_by === 'id_desc') ? 'active' : '' ?>">Newest Arrivals</a>
                <a href="products.php?<?= http_build_query(array_merge($_GET, ['sort_by' => 'price_asc', 'page' => 1])) ?>" class="list-group-item list-group-item-action <?= ($sort_by === 'price_asc') ? 'active' : '' ?>">Price: Low to High</a>
                <a href="products.php?<?= http_build_query(array_merge($_GET, ['sort_by' => 'price_desc', 'page' => 1])) ?>" class="list-group-item list-group-item-action <?= ($sort_by === 'price_desc') ? 'active' : '' ?>">Price: High to Low</a>
                <a href="products.php?<?= http_build_query(array_merge($_GET, ['sort_by' => 'name_asc', 'page' => 1])) ?>" class="list-group-item list-group-item-action <?= ($sort_by === 'name_asc') ? 'active' : '' ?>">Name: A-Z</a>
                <a href="products.php?<?= http_build_query(array_merge($_GET, ['sort_by' => 'name_desc', 'page' => 1])) ?>" class="list-group-item list-group-item-action <?= ($sort_by === 'name_desc') ? 'active' : '' ?>">Name: Z-A</a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="row">
                <?php if (empty($products)): ?>
                    <div class="alert alert-info">No products found.</div>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
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

<?php require 'footer.php'; ?>
