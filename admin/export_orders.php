<?php
require_once '../db_connect.php';
require_once 'auth.php'; // Ensure admin is logged in

// Get filter parameters from query string
$status_filter = $_GET['status'] ?? 'All';
$search_query = $_GET['search'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Build query
$sql = "SELECT o.*, oi.product_name, oi.quantity, oi.price_per_unit FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id WHERE 1=1";
$params = [];

if ($status_filter !== 'All') {
    $sql .= " AND o.order_status = :status";
    $params[':status'] = $status_filter;
}
if (!empty($search_query)) {
    $sql .= " AND (o.id LIKE :search OR o.customer_name LIKE :search OR o.customer_email LIKE :search)";
    $params[':search'] = "%$search_query%";
}
if (!empty($start_date)) {
    $sql .= " AND DATE(o.created_at) >= :start_date";
    $params[':start_date'] = $start_date;
}
if (!empty($end_date)) {
    $sql .= " AND DATE(o.created_at) <= :end_date";
    $params[':end_date'] = $end_date;
}

$sql .= " ORDER BY o.id, oi.id";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="orders_'.date('Y-m-d').'.csv"');

$output = fopen('php://output', 'w');

// Add CSV header
fputcsv($output, [
    'Order ID', 'Customer Name', 'Customer Email', 'Address', 'Pincode', 'Total Amount', 'Shipping', 'GST', 'Other Charges', 'Other Charges Description', 'Status', 'Tracking #', 'Order Date', 'Product Name', 'Quantity', 'Price Per Unit'
]);

// Add data to CSV
foreach ($results as $row) {
    fputcsv($output, [
        $row['id'],
        $row['customer_name'],
        $row['customer_email'],
        $row['customer_address'],
        $row['pincode'],
        $row['total_amount'],
        $row['shipping_charge'],
        $row['gst_amount'],
        $row['other_charges'],
        $row['other_charges_description'],
        $row['order_status'],
        $row['tracking_number'],
        $row['created_at'],
        $row['product_name'],
        $row['quantity'],
        $row['price_per_unit']
    ]);
}

fclose($output);
exit;
