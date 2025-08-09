<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php'; // Include config to get SITE_NAME and other settings

if (!isset($pageTitle)) {
    $pageTitle = SITE_NAME;
}
if (!isset($currentPage)) {
    $currentPage = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="A free HTML template by rskworld.in for launching your dropshipping store quickly.">
    <meta name="keywords" content="dropshipping, ecommerce, startup, html5, template, rskworld">
    <meta name="author" content="rskworld.in">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">

    <style>
        .autocomplete-suggestions {
            border: 1px solid #e0e0e0;
            border-top: none;
            background: #fff;
            max-height: 200px;
            overflow-y: auto;
            position: absolute;
            z-index: 999;
            width: calc(100% - 2px); /* Adjust for border */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .autocomplete-suggestion {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .autocomplete-suggestion:hover {
            background-color: #f0f0f0;
        }
        .autocomplete-suggestion:last-child {
            border-bottom: none;
        }
    </style>

</head>
<body>
    <!-- Free Template Badge -->
    <div class="badge bg-success badge-free">
        <i class="fas fa-gift"></i> Free HTML Template
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-store"></i> <?= htmlspecialchars(SITE_NAME) ?>
            </a>

            <!-- Search Form & Cart for Mobile (visible on xs, sm, md) -->
            <div class="d-flex align-items-center d-lg-none ms-auto">
                <form class="d-flex me-2" action="products.php" method="GET">
                    <input class="form-control form-control-sm" type="search" name="search" placeholder="Search..." aria-label="Search">
                </form>
                <a class="nav-link" href="checkout.php">
                    <i class="material-icons" style="font-size:24px;vertical-align:middle;">shopping_cart</i>
                    <span id="cart-count-mobile" class="badge bg-danger ms-1 <?php if (empty($_SESSION['cart'])) echo 'd-none'; ?>">
                        <?php if (!empty($_SESSION['cart'])) echo array_sum(array_column($_SESSION['cart'], 'qty')); ?>
                    </span>
                </a>
                <a class="nav-link" href="my-wishlist.php">
                    <i class="material-icons" style="font-size:24px;vertical-align:middle;">favorite</i>
                    <span id="wishlist-count-mobile" class="badge bg-danger ms-1 <?php if (empty($_SESSION['wishlist'])) echo 'd-none'; ?>">
                        <?php if (!empty($_SESSION['wishlist'])) echo count($_SESSION['wishlist']); ?>
                    </span>
                </a>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage==='home' ? 'active' : '' ?>" href="index.php">
                            <i class="material-icons" style="font-size:18px;vertical-align:middle;">home</i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage==='products' ? 'active' : '' ?>" href="products.php">
                            <i class="material-icons" style="font-size:18px;vertical-align:middle;">inventory</i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage==='subscription' ? 'active' : '' ?>" href="subscription.php">
                            <i class="material-icons" style="font-size:18px;vertical-align:middle;">subscriptions</i> Subscription
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage==='my_account' ? 'active' : '' ?>" href="my_account.php">
                                <i class="material-icons" style="font-size:18px;vertical-align:middle;">account_circle</i></a>
                        </li>
                     
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="material-icons" style="font-size:18px;vertical-align:middle;">logout</i></a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login-register.php">
                                <i class="material-icons" style="font-size:18px;vertical-align:middle;">person</i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Search Form & Cart for Desktop (visible on lg and up) -->
            <div class="d-none d-lg-flex align-items-center">
                <form class="d-flex me-2" action="products.php" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search products..." aria-label="Search">
                </form>
                <a class="nav-link" href="checkout.php">
                    <i class="material-icons" style="font-size:24px;vertical-align:middle;">shopping_cart</i>
                    <span id="cart-count" class="badge bg-danger ms-1 <?php if (empty($_SESSION['cart'])) echo 'd-none'; ?>">
                        <?php if (!empty($_SESSION['cart'])) echo array_sum(array_column($_SESSION['cart'], 'qty')); ?>
                    </span>
                </a>
                <a class="nav-link" href="my-wishlist.php">
                    <i class="material-icons" style="font-size:24px;vertical-align:middle;">favorite</i>
                    <span id="wishlist-count" class="badge bg-danger ms-1 <?php if (empty($_SESSION['wishlist'])) echo 'd-none'; ?>">
                        <?php if (!empty($_SESSION['wishlist'])) echo count($_SESSION['wishlist']); ?>
                    </span>
                </a>
            </div>
        </div>
    </nav>
    <!-- Spacer to account for fixed navbar -->
    <div style="height: 80px;"></div>

    <!-- JS Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" defer></script>

    
</body>
</html>