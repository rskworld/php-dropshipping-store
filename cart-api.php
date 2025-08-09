<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$response = ['success' => false, 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    $quantity = isset($_POST['quantity']) ? filter_var($_POST['quantity'], FILTER_VALIDATE_INT) : 1;

    error_log("Cart API: Received POST data: " . print_r($_POST, true));

    if ($productId === false || $quantity === false || $quantity < 1) {
        $response['message'] = 'Invalid product ID or quantity.';
        error_log("Cart API: Invalid product ID or quantity. Product ID: " . $productId . ", Quantity: " . $quantity);
        echo json_encode($response);
        exit;
    }

    // Fetch product details from database
    try {
        $stmt = $pdo->prepare("SELECT id, name, price, main_image FROM products WHERE id = :id");
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $response['message'] = 'Product not found.';
            error_log("Cart API: Product not found for ID: " . $productId);
            echo json_encode($response);
            exit;
        }
        error_log("Cart API: Fetched product: " . print_r($product, true));
    } catch (PDOException $e) {
        error_log("Cart API Product Fetch Error: " . $e->getMessage());
        $response['message'] = 'Database error fetching product details.';
        echo json_encode($response);
        exit;
    }

    $productName = $product['name'];
    $productPrice = $product['price'];
    $productImage = $product['main_image'];

    if (isset($_POST['action'])) {
        $action = $_POST['action'];
    } else {
        $action = 'add';
    }

    // Use product ID as key in cart session
    $cartKey = $productId;

    switch ($action) {
        case 'add':
            if (isset($_SESSION['cart'][$cartKey])) {
                $_SESSION['cart'][$cartKey]['qty'] += $quantity;
            } else {
                $_SESSION['cart'][$cartKey] = [
                    'id' => $productId,
                    'name' => $productName,
                    'price' => $productPrice,
                    'qty' => $quantity,
                    'image' => $productImage
                ];
            }
            $response['message'] = htmlspecialchars($productName) . ' added to cart.';
            break;

        case 'increase':
            if (isset($_SESSION['cart'][$cartKey])) {
                $_SESSION['cart'][$cartKey]['qty']++;
            }
            $response['message'] = 'Quantity increased.';
            break;

        case 'decrease':
            if (isset($_SESSION['cart'][$cartKey])) {
                $_SESSION['cart'][$cartKey]['qty']--;
                if ($_SESSION['cart'][$cartKey]['qty'] <= 0) {
                    unset($_SESSION['cart'][$cartKey]);
                }
            }
            $response['message'] = 'Quantity decreased.';
            break;

        case 'remove':
            if (isset($_SESSION['cart'][$cartKey])) {
                unset($_SESSION['cart'][$cartKey]);
            }
            $response['message'] = 'Product removed from cart.';
            break;
    }

    error_log("Cart API: Session cart after action: " . print_r($_SESSION['cart'], true));

    // Recalculate totals
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['qty'];
    }

    $cartIsEmpty = empty($_SESSION['cart']);
    $shipping = $cartIsEmpty ? 0 : SHIPPING_CHARGE;
    $gst = $subtotal * GST_RATE;
    $grandTotal = $subtotal + $shipping + $gst;

    $response['success'] = true;
    $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'qty'));
    $itemRemoved = !isset($_SESSION['cart'][$cartKey]);
    $response['itemRemoved'] = $itemRemoved;
    $response['newQty'] = $itemRemoved ? 0 : $_SESSION['cart'][$cartKey]['qty'];
    
    // Send all currency values formatted
    $response['subtotal'] = number_format($subtotal, 2);
    $response['shipping'] = number_format($shipping, 2);
    $response['gst'] = number_format($gst, 2);
    $response['grandTotal'] = number_format($grandTotal, 2);
    if (!$itemRemoved) {
         $response['newItemSubtotal'] = number_format($_SESSION['cart'][$cartKey]['price'] * $response['newQty'], 2);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_cart_summary') {
    // This block can be used to fetch cart summary without modifying it
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['qty'];
    }

    $cartIsEmpty = empty($_SESSION['cart']);
    $shipping = $cartIsEmpty ? 0 : SHIPPING_CHARGE;
    $gst = $subtotal * GST_RATE;
    $grandTotal = $subtotal + $shipping + $gst;

    $response['success'] = true;
    $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'qty'));
    $response['subtotal'] = number_format($subtotal, 2);
    $response['shipping'] = number_format($shipping, 2);
    $response['gst'] = number_format($gst, 2);
    $response['grandTotal'] = number_format($grandTotal, 2);
    $response['cartItems'] = array_values($_SESSION['cart']); // Send cart items as an array
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_cart_count') {
    $response['success'] = true;
    $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'qty'));
} else {
    $response['message'] = 'Invalid request method or missing parameters.';
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
