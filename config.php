<?php
require_once 'db_connect.php';

// Fetch site settings from database
$site_settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $site_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    error_log("Error fetching settings: " . $e->getMessage());
    // Fallback to default values if database fetch fails
    $site_settings = [
        'shipping_charge' => '150.00',
        'gst_rate' => '0.18',
        'site_name' => 'RSK World',
        'contact_email' => 'support@rskworld.in',
        'contact_phone' => '+1 (555) 123-4567',
        'contact_address' => 'San Francisco, CA'
    ];
}

// Define constants from fetched settings
define('SHIPPING_CHARGE', (float)$site_settings['shipping_charge']);
define('GST_RATE', (float)$site_settings['gst_rate']);
define('SITE_NAME', $site_settings['site_name']);
define('CONTACT_EMAIL', $site_settings['contact_email']);
define('CONTACT_PHONE', $site_settings['contact_phone']);
define('CONTACT_ADDRESS', $site_settings['contact_address']);

// Fetch product catalog from database only when needed
function get_catalog() {
    global $pdo;
    static $catalog = null;
    if ($catalog === null) {
        $catalog = [];
        try {
            $stmt = $pdo->query("SELECT id, name, price, icon, main_image FROM products");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $catalog[$row['name']] = [
                    'id' => $row['id'],
                    'price' => (float)$row['price'],
                    'icon' => $row['icon'],
                    'thumbnail' => $row['main_image'] // Use main_image as thumbnail for catalog
                ];
            }
        } catch (PDOException $e) {
            error_log("Error fetching products: " . $e->getMessage());
            $catalog = [];
        }
    }
    return $catalog;
}

