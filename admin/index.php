<?php
$adminPageTitle = 'Dashboard';
$currentAdminPage = 'dashboard';
require_once 'header.php';
require_once '../db_connect.php';

// Initialize all chart data arrays to prevent errors
$sales_labels = $sales_values = $top_product_labels = $top_product_values = [];
$order_status_labels = $order_status_values = $order_status_backgrounds = [];
$monthly_sales_labels = $monthly_sales_values = [];
$new_users_labels = $new_users_values = [];
$sales_by_category_labels = $sales_by_category_values = [];
$avg_order_value_labels = $avg_order_value_values = [];
$sales_by_location_labels = $sales_by_location_values = [];
$sales_by_payment_method_labels = $sales_by_payment_method_values = [];

$error_message = null;
$last_query = '';

try {
    $last_query = "Total Sales";
    $total_sales = $pdo->query("SELECT SUM(total_amount) AS total_sales FROM orders WHERE order_status != 'Cancelled'")->fetchColumn() ?? 0;

    $last_query = "New Orders Today";
    $new_orders_today = $pdo->query("SELECT COUNT(*) AS new_orders FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?? 0;

    $last_query = "Products in Stock";
    $products_in_stock = $pdo->query("SELECT COUNT(*) AS total_products FROM products")->fetchColumn() ?? 0;

    $last_query = "Total Customers";
    $total_customers = $pdo->query("SELECT COUNT(*) AS total_users FROM users")->fetchColumn() ?? 0;

    $last_query = "Sales data for chart (last 7 days)";
    $sales_data_raw = $pdo->query("SELECT DATE(created_at) as order_date, SUM(total_amount) as daily_sales FROM orders WHERE created_at >= CURDATE() - INTERVAL 7 DAY GROUP BY order_date ORDER BY order_date ASC")->fetchAll(PDO::FETCH_ASSOC);
    $period = new DatePeriod(new DateTime('-7 days'), new DateInterval('P1D'), new DateTime('+1 day'));
    $daily_sales_map = array_column($sales_data_raw, 'daily_sales', 'order_date');
    foreach ($period as $date) {
        $formatted_date = $date->format('Y-m-d');
        $sales_labels[] = $date->format('M d');
        $sales_values[] = $daily_sales_map[$formatted_date] ?? 0;
    }

    $last_query = "Top 5 Selling Products";
    $top_products_raw = $pdo->query("SELECT oi.product_name, SUM(oi.quantity) as total_quantity FROM order_items oi GROUP BY oi.product_name ORDER BY total_quantity DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $top_product_labels = array_column($top_products_raw, 'product_name');
    $top_product_values = array_column($top_products_raw, 'total_quantity');

    $last_query = "Order Status Distribution";
    $order_status_raw = $pdo->query("SELECT order_status, COUNT(*) as status_count FROM orders GROUP BY order_status")->fetchAll(PDO::FETCH_ASSOC);
    $order_status_labels = array_column($order_status_raw, 'order_status');
    $order_status_values = array_column($order_status_raw, 'status_count');
    $status_colors = ['Pending' => '#ffc107', 'Processing' => '#0dcaf0', 'Shipped' => '#198754', 'Delivered' => '#6f42c1', 'Cancelled' => '#dc3545'];
    $order_status_backgrounds = array_map(fn($status) => $status_colors[$status] ?? '#adb5bd', $order_status_labels);

    $last_query = "Monthly Sales Trend";
    $monthly_sales_raw = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as sales_month, SUM(total_amount) as monthly_sales FROM orders WHERE created_at >= CURDATE() - INTERVAL 12 MONTH GROUP BY sales_month ORDER BY sales_month ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($monthly_sales_raw as $row) {
        $monthly_sales_labels[] = date('M Y', strtotime($row['sales_month'] . '-01'));
        $monthly_sales_values[] = $row['monthly_sales'];
    }

    $last_query = "New User Registrations (Last 7 Days)";
    $new_users_raw = $pdo->query("SELECT DATE(created_at) as registration_date, COUNT(*) as new_users FROM users WHERE created_at >= CURDATE() - INTERVAL 7 DAY GROUP BY registration_date ORDER BY registration_date ASC")->fetchAll(PDO::FETCH_ASSOC);
    $user_period = new DatePeriod(new DateTime('-7 days'), new DateInterval('P1D'), new DateTime('+1 day'));
    $daily_users_map = array_column($new_users_raw, 'new_users', 'registration_date');
    foreach ($user_period as $date) {
        $formatted_date = $date->format('Y-m-d');
        $new_users_labels[] = $date->format('M d');
        $new_users_values[] = $daily_users_map[$formatted_date] ?? 0;
    }

    $last_query = "Sales by Category";
    $sales_by_category_raw = $pdo->query("SELECT c.name as category_name, SUM(oi.quantity * oi.price_per_unit) as category_sales FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN categories c ON p.category_id = c.id GROUP BY c.name ORDER BY category_sales DESC")->fetchAll(PDO::FETCH_ASSOC);
    $sales_by_category_labels = array_column($sales_by_category_raw, 'category_name');
    $sales_by_category_values = array_column($sales_by_category_raw, 'category_sales');

    $last_query = "Average Order Value";
    $avg_order_value_raw = $pdo->query("SELECT DATE(created_at) as order_date, AVG(total_amount) as avg_value FROM orders WHERE created_at >= CURDATE() - INTERVAL 7 DAY GROUP BY order_date ORDER BY order_date ASC")->fetchAll(PDO::FETCH_ASSOC);
    $avg_order_period = new DatePeriod(new DateTime('-7 days'), new DateInterval('P1D'), new DateTime('+1 day'));
    $daily_avg_value_map = array_column($avg_order_value_raw, 'avg_value', 'order_date');
    foreach ($avg_order_period as $date) {
        $formatted_date = $date->format('Y-m-d');
        $avg_order_value_labels[] = $date->format('M d');
        $avg_order_value_values[] = $daily_avg_value_map[$formatted_date] ?? 0;
    }

    $last_query = "Sales by Location";
    $sales_by_location_raw = $pdo->query("SELECT pincode, SUM(total_amount) as location_sales FROM orders WHERE pincode IS NOT NULL AND pincode != '' GROUP BY pincode ORDER BY location_sales DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $sales_by_location_labels = array_column($sales_by_location_raw, 'pincode');
    $sales_by_location_values = array_column($sales_by_location_raw, 'location_sales');

    $last_query = "Sales by Payment Method";
    $sales_by_payment_method_raw = $pdo->query("SELECT payment_method, SUM(total_amount) as method_sales FROM orders WHERE payment_method IS NOT NULL AND payment_method != '' GROUP BY payment_method ORDER BY method_sales DESC")->fetchAll(PDO::FETCH_ASSOC);
    $sales_by_payment_method_labels = array_column($sales_by_payment_method_raw, 'payment_method');
    $sales_by_payment_method_values = array_column($sales_by_payment_method_raw, 'method_sales');

} catch (PDOException $e) {
    $error_message = "Database Error while fetching data for: <strong>" . $last_query . "</strong><br>" . htmlspecialchars($e->getMessage());
}
?>

<div class="container-fluid">
    <h1 class="mb-4">Dashboard</h1>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-rupee-sign"></i></div>
                <div class="value">₹ <?= number_format($total_sales, 2) ?></div>
                <div class="label">Total Sales</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="value"><?= $new_orders_today ?></div>
                <div class="label">New Orders Today</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-users"></i></div>
                <div class="value"><?= $total_customers ?></div>
                <div class="label">Total Customers</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-boxes"></i></div>
                <div class="value"><?= $products_in_stock ?></div>
                <div class="label">Products in Stock</div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">Sales Over Last 7 Days</div>
        <div class="card-body">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Top 5 Selling Products</div>
                <div class="card-body">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Order Status Distribution</div>
                <div class="card-body">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Sales by Category</div>
                <div class="card-body">
                    <canvas id="salesByCategoryChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Average Order Value (Last 7 Days)</div>
                <div class="card-body">
            <canvas id="avgOrderValueChart"></canvas>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">Top 10 Sales Locations (by Pincode)</div>
    <div class="card-body">
        <canvas id="salesByLocationChart"></canvas>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">Sales by Payment Method</div>
    <div class="card-body">
        <canvas id="salesByPaymentMethodChart"></canvas>
    </div>
</div>

<div class="card mt-4">
        <div class="card-header">Monthly Sales Trend</div>
        <div class="card-body">
            <canvas id="monthlySalesChart"></canvas>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">New User Registrations (Last 7 Days)</div>
        <div class="card-body">
            <canvas id="newUsersChart"></canvas>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">Recent Activity</div>
        <div class="card-body">
            <p>No recent activity to display.</p>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($sales_labels) ?>,
                datasets: [{
                    label: 'Daily Sales (₹)',
                    data: <?= json_encode($sales_values) ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Sales (₹)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });
    }

    const topProductsCtx = document.getElementById('topProductsChart');
    if (topProductsCtx) {
        new Chart(topProductsCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($top_product_labels) ?>,
                datasets: [{
                    label: 'Quantity Sold',
                    data: <?= json_encode($top_product_values) ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity'
                        }
                    }
                }
            }
        });
    }

    const orderStatusCtx = document.getElementById('orderStatusChart');
    if (orderStatusCtx) {
        new Chart(orderStatusCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode($order_status_labels) ?>,
                datasets: [{
                    label: 'Order Status',
                    data: <?= json_encode($order_status_values) ?>,
                    backgroundColor: <?= json_encode($order_status_backgrounds) ?>,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Order Status Distribution'
                    }
                }
            }
        });
    }

    const monthlySalesCtx = document.getElementById('monthlySalesChart');
    if (monthlySalesCtx) {
        new Chart(monthlySalesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($monthly_sales_labels) ?>,
                datasets: [{
                    label: 'Monthly Sales (₹)',
                    data: <?= json_encode($monthly_sales_values) ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Sales (₹)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    }

    const newUsersCtx = document.getElementById('newUsersChart');
    if (newUsersCtx) {
        new Chart(newUsersCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($new_users_labels) ?>,
                datasets: [{
                    label: 'New Registrations',
                    data: <?= json_encode($new_users_values) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Users'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });
    }

    const salesByCategoryCtx = document.getElementById('salesByCategoryChart');
    if (salesByCategoryCtx) {
        new Chart(salesByCategoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($sales_by_category_labels) ?>,
                datasets: [{
                    label: 'Sales by Category',
                    data: <?= json_encode($sales_by_category_values) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
            }
        });
    }

    const avgOrderValueCtx = document.getElementById('avgOrderValueChart');
    if (avgOrderValueCtx) {
        new Chart(avgOrderValueCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($avg_order_value_labels) ?>,
                datasets: [{
                    label: 'Average Order Value (₹)',
                    data: <?= json_encode($avg_order_value_values) ?>,
                    borderColor: 'rgb(153, 102, 255)',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (₹)'
                        }
                    }
                }
            }
        });
    }

    const salesByLocationCtx = document.getElementById('salesByLocationChart');
    if (salesByLocationCtx) {
        new Chart(salesByLocationCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($sales_by_location_labels) ?>,
                datasets: [{
                    label: 'Total Sales (₹)',
                    data: <?= json_encode($sales_by_location_values) ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Sales (₹)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Pincode'
                        }
                    }
                }
            }
        });
    }

    const salesByPaymentMethodCtx = document.getElementById('salesByPaymentMethodChart');
    if (salesByPaymentMethodCtx) {
        new Chart(salesByPaymentMethodCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode($sales_by_payment_method_labels) ?>,
                datasets: [{
                    label: 'Sales by Payment Method',
                    data: <?= json_encode($sales_by_payment_method_values) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
            }
        });
    }
});
</script>

<?php require_once 'footer.php'; ?>