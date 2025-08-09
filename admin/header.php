<?php
require_once 'auth.php';

$adminPageTitle = $adminPageTitle ?? 'Admin Dashboard';
$currentAdminPage = $currentAdminPage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminPageTitle) ?> | Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --admin-primary: #4a4a4a; /* Dark grey for primary elements */
            --admin-secondary: #f0f2f5; /* Light grey for backgrounds */
            --admin-text: #333333;
            --admin-light-text: #ffffff;
            --admin-sidebar-bg: #2c3e50; /* Dark blue-grey */
            --admin-sidebar-hover: #34495e;
            --admin-accent: #007bff; /* Bootstrap primary blue */
            --admin-card-bg: #ffffff;
            --admin-border: #e0e0e0;
        }
        body {
            font-family: 'Inter', 'Poppins', -apple-system, BlinkMacMacFont, sans-serif;
            background-color: var(--admin-secondary);
            color: var(--admin-text);
            margin: 0;
        }
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: var(--admin-sidebar-bg);
            color: var(--admin-light-text);
            padding-top: 20px;
            flex-shrink: 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: var(--admin-light-text);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: background-color 0.3s ease, border-left-color 0.3s ease;
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: var(--admin-sidebar-hover);
            border-left-color: var(--admin-accent);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .navbar-admin {
            background-color: var(--admin-card-bg);
            box-shadow: 0 2px 4px rgba(0,0,0,.08);
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 15px 20px;
        }
        .navbar-admin .form-control {
            border-radius: 20px;
            border-color: var(--admin-border);
        }
        .navbar-admin .btn-outline-secondary {
            border-radius: 20px;
            border-color: var(--admin-border);
        }
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .card-header {
            background-color: var(--admin-card-bg);
            border-bottom: 1px solid var(--admin-border);
            font-weight: 600;
            padding: 15px 20px;
        }
        .card-body {
            padding: 20px;
        }
        .stat-card {
            background-color: var(--admin-card-bg);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .stat-card .icon {
            font-size: 3rem;
            color: var(--admin-accent);
            margin-bottom: 10px;
        }
        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--admin-text);
        }
        .stat-card .label {
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <nav class="sidebar">
            <h4 class="text-center mb-4" style="color: var(--admin-light-text);">Admin Panel</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= $currentAdminPage === 'dashboard' ? 'active' : '' ?>" href="index.php">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentAdminPage === 'products' ? 'active' : '' ?>" href="products.php">
                        <i class="fas fa-box me-2"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentAdminPage === 'categories' ? 'active' : '' ?>" href="categories.php">
                        <i class="fas fa-tags me-2"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentAdminPage === 'orders' ? 'active' : '' ?>" href="orders.php">
                        <i class="fas fa-shopping-cart me-2"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentAdminPage === 'users' ? 'active' : '' ?>" href="users.php">
                        <i class="fas fa-users me-2"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentAdminPage === 'feedback' ? 'active' : '' ?>" href="feedback.php">
                        <i class="fas fa-comments me-2"></i> Feedback
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentAdminPage === 'subscribers' ? 'active' : '' ?>" href="subscribers.php">
                        <i class="fas fa-envelope-open-text me-2"></i> Subscribers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentAdminPage === 'settings' ? 'active' : '' ?>" href="settings.php">
                        <i class="fas fa-cog me-2"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentAdminPage === 'account' ? 'active' : '' ?>" href="account.php">
                        <i class="fas fa-user-cog me-2"></i> Account
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?logout=true">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
        <div class="content">
            <nav class="navbar navbar-expand-lg navbar-light navbar-admin">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#" style="color: var(--admin-text); font-weight: 700;">Dashboard</a>
                    <div class="collapse navbar-collapse" id="adminNavbarContent">
                        <form class="d-flex ms-auto me-3">
                            <input class="form-control me-2" type="search" placeholder="Search..." aria-label="Search">
                            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        </form>
                        <ul class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--admin-text);">
                                    <i class="fas fa-user-circle fa-lg"></i> Admin
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="account.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="?logout=true">Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
