<?php
require_once 'db_connect.php';

echo "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Database Setup</title><link href=\"https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css\" rel=\"stylesheet\"></head><body><div class=\"container mt-5\"><h1>Database Setup Script</h1>";

$sql_commands = [
    // Drop tables in the correct order to avoid foreign key constraint errors
    "DROP TABLE IF EXISTS order_items;",
    "DROP TABLE IF EXISTS product_images;",
    "DROP TABLE IF EXISTS reviews;",
    "DROP TABLE IF EXISTS wishlist;",
    "DROP TABLE IF EXISTS user_addresses;",
    "DROP TABLE IF EXISTS orders;",
    "DROP TABLE IF EXISTS products;",
    "DROP TABLE IF EXISTS categories;",
    "DROP TABLE IF EXISTS feedback;",
    "DROP TABLE IF EXISTS admin_users;",
    "DROP TABLE IF EXISTS settings;",
    "DROP TABLE IF EXISTS users;",
    "DROP TABLE IF EXISTS subscribers;",

    "CREATE TABLE `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `subscription_plan` VARCHAR(50) DEFAULT 'Starter',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );",

    "CREATE TABLE `user_addresses` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `address` TEXT NOT NULL,
        `pincode` VARCHAR(10) NOT NULL,
        `landmark` VARCHAR(255) NULL,
        `is_default` BOOLEAN DEFAULT FALSE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    );",

    "CREATE TABLE `categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL UNIQUE
    );",

    "INSERT IGNORE INTO `categories` (`name`) VALUES ('Electronics'), ('Fashion'), ('Home & Garden'), ('Health & Beauty');",

    "CREATE TABLE `products` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `category_id` INT,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `details` TEXT,
        `price` DECIMAL(10, 2) NOT NULL,
        `icon` VARCHAR(50),
        `main_image` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
    );",

    "INSERT INTO `products` (`name`, `description`, `details`, `price`, `icon`, `main_image`, `category_id`) VALUES
    ('Laptop Computer', 'High-performance laptop for professionals', 'A powerful laptop designed for demanding tasks, featuring a high-resolution display, fast processor, and ample storage. Ideal for professionals and students alike.', 74999.00, 'laptop', 'https://placehold.co/300x200/E0E7FF/4F46E5/png?text=Laptop%0Arskworld.in', 1),
    ('Smartphone', 'Latest smartphone with advanced features', 'Experience the next generation of mobile technology with this cutting-edge smartphone. Boasting an incredible camera, long-lasting battery, and a vibrant display, it\'s perfect for staying connected and productive.', 49999.00, 'mobile-alt', 'https://placehold.co/300x200/E0E7FF/4F46E5/png?text=Smartphone%0Arskworld.in', 1),
    ('Smart Watch', 'Fitness tracking and notifications', 'Track your fitness goals and stay connected on the go with this stylish smart watch. Features include heart rate monitoring, step tracking, and smart notifications directly to your wrist.', 14999.00, 'watch', 'https://placehold.co/300x200/E0E7FF/4F46E5/png?text=Smart+Watch%0Arskworld.in', 1),
    ('Wireless Headphones', 'Premium sound quality headphones', 'Immerse yourself in rich, clear audio with these wireless headphones. Designed for comfort and superior sound, they are perfect for music lovers and audiophiles.', 4999.00, 'headphones', 'https://placehold.co/300x200/E0E7FF/4F46E5/png?text=Wireless+Headphones%0Arskworld.in', 1);",

    "CREATE TABLE `subscribers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );",

    "CREATE TABLE `reviews` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `product_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `rating` INT NOT NULL,
        `review` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    );",

    "CREATE TABLE `admin_users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) UNIQUE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );",

    "CREATE TABLE `settings` (
        `setting_key` VARCHAR(255) PRIMARY KEY,
        `setting_value` TEXT,
        `description` TEXT,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );",

    "INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
    ('site_name', 'RSK World', 'The name of the website/store.'),
    ('contact_email', 'support@rskworld.in', 'Primary contact email address.'),
    ('contact_phone', '+1 (555) 123-4567', 'Primary contact phone number.'),
    ('contact_address', 'San Francisco, CA', 'Physical address for contact page.'),
    ('shipping_charge', '150.00', 'Fixed shipping charge in INR.'),
    ('gst_rate', '0.18', 'GST rate as a decimal (e.g., 0.18 for 18%).');",

    "CREATE TABLE `feedback` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `first_name` VARCHAR(255) NOT NULL,
        `last_name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `subject` VARCHAR(255),
        `message` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `is_read` BOOLEAN DEFAULT FALSE
    );",

    "CREATE TABLE `orders` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NULL,
        `customer_name` VARCHAR(255) NOT NULL,
        `customer_email` VARCHAR(255) NOT NULL,
        `customer_address` TEXT,
        `pincode` VARCHAR(10) NULL,
        `landmark` VARCHAR(255) NULL,
        `total_amount` DECIMAL(10, 2) NOT NULL,
        `shipping_charge` DECIMAL(10, 2) NOT NULL,
        `gst_amount` DECIMAL(10, 2) NOT NULL,
        `payment_method` VARCHAR(50) DEFAULT NULL,
        `tracking_number` VARCHAR(255) NULL,
        `order_status` VARCHAR(50) DEFAULT 'Pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
    );",

    "ALTER TABLE `orders` ADD COLUMN `other_charges` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER `gst_amount`;",
    "ALTER TABLE `orders` ADD COLUMN `other_charges_description` VARCHAR(255) NULL AFTER `other_charges`;",

    "CREATE TABLE `order_items` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `order_id` INT NOT NULL,
        `product_id` INT NOT NULL,
        `product_name` VARCHAR(255) NOT NULL,
        `quantity` INT NOT NULL,
        `price_per_unit` DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
    );",

    "CREATE TABLE `product_images` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `product_id` INT NOT NULL,
        `image_path` VARCHAR(255) NOT NULL,
        `sort_order` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
    );",

    "CREATE TABLE `wishlist` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `product_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY (`user_id`, `product_id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
    );"
];

function columnExists($pdo, $tableName, $columnName) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$columnName}'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

foreach ($sql_commands as $command) {
    try {
        // More robust checks for ALTER TABLE commands
        if (stripos($command, 'ADD COLUMN') !== false) {
            preg_match('/ADD COLUMN `(.*?)`/', $command, $matches);
            $columnName = $matches[1];
            preg_match('/TABLE `(.*?)`/', $command, $matches);
            $tableName = $matches[1];
            if (!columnExists($pdo, $tableName, $columnName)) {
                $pdo->exec($command);
                echo "<div class=\"alert alert-success\">Successfully executed: <pre>" . htmlspecialchars($command) . "</pre></div>";
            } else {
                echo "<div class=\"alert alert-info\">Column '{$columnName}' already exists in table '{$tableName}'. Skipping.</div>";
            }
        } else {
            $pdo->exec($command);
            echo "<div class=\"alert alert-success\">Successfully executed: <pre>" . htmlspecialchars($command) . "</pre></div>";
        }
    } catch (PDOException $e) {
        echo "<div class=\"alert alert-danger\">Error executing: <pre>" . htmlspecialchars($command) . "</pre><br>" . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Generate and display hashed password for admin
$admin_password = 'password';
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Automatically insert or update the admin user
$admin_username = 'admin';
$admin_password = 'password';
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
$admin_email = 'admin@example.com';

try {
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = :username");
    $stmt->execute(['username' => $admin_username]);
    $user = $stmt->fetch();

    if ($user) {
        // Update existing admin user's password
        $stmt_update = $pdo->prepare("UPDATE admin_users SET password_hash = :password_hash WHERE username = :username");
        $stmt_update->execute(['password_hash' => $hashed_password, 'username' => $admin_username]);
        echo "<div class=\"alert alert-success\">Admin user already exists. Password has been updated to 'password'.</div>";
    } else {
        // Insert new admin user
        $stmt_insert = $pdo->prepare("INSERT INTO admin_users (username, password_hash, email) VALUES (:username, :password_hash, :email)");
        $stmt_insert->execute(['username' => $admin_username, 'password_hash' => $hashed_password, 'email' => $admin_email]);
        echo "<div class=\"alert alert-success\">Admin user created successfully. Username: 'admin', Password: 'password'.</div>";
    }
} catch (PDOException $e) {
    echo "<div class=\"alert alert-danger\">Error setting up admin user: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<p class=\"mt-4\"><a href=\"admin/login.php\" class_=\"btn btn-primary\">Go to Admin Login</a></p>";
echo "</div></body></html>";
?>