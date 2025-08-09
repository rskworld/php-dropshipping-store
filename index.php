<?php
$pageTitle = 'Home | RSK Dropshipping Template';
$currentPage = 'home';
require 'header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Launch Your Dropshipping Store Today</h1>
                <p class="lead mb-4">Complete HTML template with all the tools you need to start your dropshipping business. Built by rskworld.in for entrepreneurs.</p>
                <div class="d-flex gap-3">
                    <a href="products.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-cart"></i> View Products
                    </a>
                    <a href="about.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle"></i> Learn More
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="fas fa-rocket" style="font-size:15rem;opacity:0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title">Dropshipping Features</h2>
        <div class="row">
            <!-- Supplier Integration -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4 class="card-title">Supplier Integration</h4>
                        <p class="card-text">Connect with trusted suppliers worldwide. Automated integration with major dropshipping platforms.</p>
                        <div class="integration-card">
                            <h6><i class="fas fa-plug"></i> AliExpress API</h6>
                            <span class="status-badge status-active">Connected</span>
                        </div>
                        <div class="integration-card">
                            <h6><i class="fas fa-plug"></i> Oberlo Integration</h6>
                            <span class="status-badge status-pending">Pending</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Automated Order Fulfillment -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h4 class="card-title">Order Fulfillment</h4>
                        <p class="card-text">Automated order processing and fulfillment. Real-time order tracking and status updates.</p>
                        <div class="table-container">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>#1001</td>
                                        <td><span class="status-badge status-active">Shipped</span></td>
                                        <td><i class="fas fa-check"></i></td>
                                    </tr>
                                    <tr>
                                        <td>#1002</td>
                                        <td><span class="status-badge status-pending">Processing</span></td>
                                        <td><i class="fas fa-clock"></i></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Product Import Tools -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <h4 class="card-title">Product Import</h4>
                        <p class="card-text">One-click product import from suppliers. Bulk import with CSV and API integration.</p>
                        <div class="table-container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6>Import Queue</h6>
                                <button class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Import
                                </button>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar" style="width: 75%">75%</div>
                            </div>
                            <small class="text-muted">Processing 150 of 200 products</small>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Price Markup Management -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <h4 class="card-title">Price Management</h4>
                        <p class="card-text">Dynamic pricing with markup rules. Automatic price updates and competitive pricing.</p>
                        <div class="table-container">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Markup</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Electronics</td>
                                        <td>25%</td>
                                        <td><span class="status-badge status-active">Active</span></td>
                                    </tr>
                                    <tr>
                                        <td>Fashion</td>
                                        <td>40%</td>
                                        <td><span class="status-badge status-active">Active</span></td>
                                    </tr>
                                    <tr>
                                        <td>Home & Garden</td>
                                        <td>30%</td>
                                        <td><span class="status-badge status-pending">Pending</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Inventory Tracking -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h4 class="card-title">Inventory Tracking</h4>
                        <p class="card-text">Real-time inventory synchronization. Low stock alerts and automated reordering.</p>
                        <div class="table-container">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="stat-number">1,247</div>
                                    <small class="text-muted">Total Products</small>
                                </div>
                                <div class="col-4">
                                    <div class="stat-number">23</div>
                                    <small class="text-muted">Low Stock</small>
                                </div>
                                <div class="col-4">
                                    <div class="stat-number">5</div>
                                    <small class="text-muted">Out of Stock</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Mobile-Friendly Design -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4 class="card-title">Mobile-Friendly</h4>
                        <p class="card-text">Responsive design that works perfectly on all devices. Mobile-optimized checkout process.</p>
                        <div class="table-container">
                            <div class="row text-center">
                                <div class="col-6">
                                    <i class="fas fa-desktop fa-2x text-primary mb-2"></i>
                                    <div>Desktop</div>
                                    <small class="text-success">✓ Optimized</small>
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-mobile fa-2x text-primary mb-2"></i>
                                    <div>Mobile</div>
                                    <small class="text-success">✓ Responsive</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Multi-Channel Sales -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <h4 class="card-title">Multi-Channel Sales</h4>
                        <p class="card-text">Sell on Amazon, eBay, and social media with centralized inventory management.</p>
                    </div>
                </div>
            </div>
            <!-- Analytics Dashboard -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 class="card-title">Analytics Dashboard</h4>
                        <p class="card-text">Gain deep insights into sales, traffic, and customer behavior with real-time analytics.</p>
                    </div>
                </div>
            </div>
            <!-- 24/7 Support -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4 class="card-title">24/7 Support</h4>
                        <p class="card-text">Our dedicated team is ready around the clock to help your business succeed.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <h2 class="section-title">Why Choose Our Template?</h2>
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="stat-item">
                    <span class="stat-number">10,000+</span>
                    <h5>Downloads</h5>
                    <p>Trusted by entrepreneurs worldwide</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-item">
                    <span class="stat-number">500+</span>
                    <h5>Suppliers</h5>
                    <p>Integrated supplier network</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-item">
                    <span class="stat-number">99.9%</span>
                    <h5>Uptime</h5>
                    <p>Reliable and fast performance</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <h5>Support</h5>
                    <p>Always here to help you succeed</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
