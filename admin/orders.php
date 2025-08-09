<?php
$adminPageTitle = 'Orders';
$currentAdminPage = 'orders';
require_once 'header.php';
require_once '../db_connect.php';

$message = '';
$message_type = '';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
    $new_status = trim($_POST['new_status']);
    $tracking_number = trim($_POST['tracking_number'] ?? '');

    if ($order_id && !empty($new_status)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET order_status = :new_status, tracking_number = :tracking_number WHERE id = :order_id");
            $stmt->execute([
                'new_status' => $new_status,
                'tracking_number' => $tracking_number,
                'order_id' => $order_id
            ]);
            $message = 'Order #' . $order_id . ' updated successfully!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error updating order: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Filtering and Pagination
$status_filter = $_GET['status'] ?? 'All';
$search_query = $_GET['search'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT o.*, GROUP_CONCAT(CONCAT(oi.product_name, ' (', oi.quantity, ')') SEPARATOR '<br>') as items_summary FROM orders o LEFT JOIN order_items oi ON o.id = oi.id WHERE 1=1";
$count_sql = "SELECT COUNT(*) FROM orders WHERE 1=1";
$params = [];

if ($status_filter !== 'All') {
    $sql .= " AND o.order_status = :status";
    $count_sql .= " AND order_status = :status";
    $params[':status'] = $status_filter;
}
if (!empty($search_query)) {
    $sql .= " AND (o.id LIKE :search OR o.customer_name LIKE :search OR o.customer_email LIKE :search)";
    $count_sql .= " AND (id LIKE :search OR customer_name LIKE :search OR customer_email LIKE :search)";
    $params[':search'] = "%" . $search_query . "%";
}
if (!empty($start_date)) {
    $sql .= " AND DATE(o.created_at) >= :start_date";
    $count_sql .= " AND DATE(created_at) >= :start_date";
    $params[':start_date'] = $start_date;
}
if (!empty($end_date)) {
    $sql .= " AND DATE(o.created_at) <= :end_date";
    $count_sql .= " AND DATE(created_at) <= :end_date";
    $params[':end_date'] = $end_date;
}

$sql .= " GROUP BY o.id ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";

// Fetch total count for pagination
$stmt_count = $pdo->prepare($count_sql);
$stmt_count->execute($params);
$total_orders = $stmt_count->fetchColumn();
$total_pages = ceil($total_orders / $limit);

// Fetch orders
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid">
    <h1 class="mb-4">Order Management</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="orders.php" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Order ID, Name, Email...">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="All" <?= ($status_filter == 'All') ? 'selected' : '' ?>>All</option>
                        <option value="Pending" <?= ($status_filter == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Processing" <?= ($status_filter == 'Processing') ? 'selected' : '' ?>>Processing</option>
                        <option value="Shipped" <?= ($status_filter == 'Shipped') ? 'selected' : '' ?>>Shipped</option>
                        <option value="Delivered" <?= ($status_filter == 'Delivered') ? 'selected' : '' ?>>Delivered</option>
                        <option value="Cancelled" <?= ($status_filter == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-md-2">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="orders.php" class="btn btn-secondary">Reset</a>
                    <a href="export_orders.php?<?= http_build_query($_GET) ?>" class="btn btn-success"><i class="fas fa-file-csv"></i> Export</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Order List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tracking #</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr><td colspan="7" class="text-center">No orders found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['id']) ?></td>
                                    <td><?= htmlspecialchars($order['customer_name']) ?><br><small class="text-muted"><?= htmlspecialchars($order['customer_email']) ?></small></td>
                                    <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                    <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($order['order_status']) ?></span></td>
                                    <td><?= htmlspecialchars($order['tracking_number'] ?? 'N/A') ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info view-details-btn" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" data-order='<?= json_encode($order) ?>'>
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="modal-order-content"></div>
        <hr>
        <h5>Update Order</h5>
        <form method="POST" action="orders.php?<?= http_build_query($_GET) ?>">
            <input type="hidden" name="order_id" id="modal_order_id">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="modal_new_status" class="form-label">Order Status</label>
                    <select name="new_status" id="modal_new_status" class="form-select">
                        <option value="Pending">Pending</option>
                        <option value="Processing">Processing</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="modal_tracking_number" class="form-label">Tracking Number</label>
                    <input type="text" name="tracking_number" id="modal_tracking_number" class="form-control">
                </div>
            </div>
            <button type="submit" name="update_status" class="btn btn-primary">Update Order</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var orderDetailsModal = document.getElementById('orderDetailsModal');
    orderDetailsModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var orderData = JSON.parse(button.getAttribute('data-order'));

        var modalTitle = orderDetailsModal.querySelector('.modal-title');
        modalTitle.textContent = `Order Details for #${orderData.id}`;

        var content = `
            <h5>Customer Details:</h5>
            <p>
                <strong>Name:</strong> ${orderData.customer_name}<br>
                <strong>Email:</strong> ${orderData.customer_email}<br>
                <strong>Address:</strong> ${orderData.customer_address.replace(/\n/g, '<br>')}<br>
                <strong>Pincode:</strong> ${orderData.pincode}<br>
                <strong>Landmark:</strong> ${orderData.landmark}
            </p>
            <h5>Order Summary:</h5>
            <p>${orderData.items_summary}</p>
            <hr>
            <div class="row">
                <div class="col-md-3"><strong>Subtotal:</strong> ₹${(orderData.total_amount - orderData.shipping_charge - orderData.gst_amount - orderData.other_charges).toFixed(2)}</div>
                <div class="col-md-3"><strong>Shipping:</strong> ₹${parseFloat(orderData.shipping_charge).toFixed(2)}</div>
                <div class="col-md-3"><strong>GST:</strong> ₹${parseFloat(orderData.gst_amount).toFixed(2)}</div>
                <div class="col-md-3"><strong>Other Charges:</strong> ₹${parseFloat(orderData.other_charges).toFixed(2)}</div>
            </div>
            <h5 class="mt-2"><strong>Grand Total: ₹${parseFloat(orderData.total_amount).toFixed(2)}</strong></h5>
        `;

        document.getElementById('modal-order-content').innerHTML = content;
        document.getElementById('modal_order_id').value = orderData.id;
        document.getElementById('modal_new_status').value = orderData.order_status;
        document.getElementById('modal_tracking_number').value = orderData.tracking_number || '';
        document.getElementById('modal_shipping_charge').value = parseFloat(orderData.shipping_charge).toFixed(2);
        document.getElementById('modal_other_charges').value = parseFloat(orderData.other_charges).toFixed(2);
        document.getElementById('modal_other_charges_description').value = orderData.other_charges_description || '';
    });
});
</script>

<?php require_once 'footer.php'; ?>