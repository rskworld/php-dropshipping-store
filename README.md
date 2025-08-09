# Cart API - A PHP E-commerce & Dropshipping Platform

![PHP Version](https://img.shields.io/badge/php-%3E=7.4-blue)
![Database](https://img.shields.io/badge/database-MySQL-orange)
![License](https://img.shields.io/badge/license-MIT-green)
![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)

A full-featured, SEO-friendly e-commerce and dropshipping platform built with PHP and MySQL. This project provides a complete solution for launching an online store, featuring a customer-facing storefront and a powerful admin panel for store management. This is a free template provided by rskworld.in.

## Key Features

### Customer-Facing Features
- **Dynamic Product Catalog:** Browse products with advanced search, filtering (by category, price), and sorting options.
- **Detailed Product Pages:** View products with multiple images, detailed descriptions, customer reviews, and related products.
- **AJAX-Powered Shopping Cart:** A seamless and interactive shopping cart experience without page reloads.
- **Streamlined Checkout:** An easy-to-use, multi-step checkout process with address and payment method selection.
- **User Authentication:** Secure customer registration and login functionality.
- **Personalized User Accounts:** A dedicated account section for users to view their order history, manage shipping addresses, and update their profile.
- **Wishlist Functionality:** Save favorite products for future purchase.
- **Subscription Model:** Built-in support for tiered subscription plans.
- **Content Pages:** Pre-built pages for About Us, Contact, FAQ, and Testimonials.

### Admin Panel Features
- **Analytical Dashboard:** A comprehensive dashboard with key metrics, including total sales, new orders, customer count, and product stock, visualized with charts.
- **Product Management:** A complete CRUD (Create, Read, Update, Delete) interface for products, including management of images, descriptions, and stock levels.
- **Category Management:** Easily add, edit, and delete product categories.
- **Order Management:** A detailed view of all customer orders with the ability to update order status and tracking numbers.
- **Customer Management:** View and manage all registered customers.
- **Feedback Management:** A dedicated section to view and manage messages submitted through the contact form.
- **Newsletter Subscribers:** A list of all users who have subscribed to the newsletter.
- **Site Configuration:** A settings panel to manage general site information, such as the site name, contact details, shipping charges, and GST rates.
- **Secure Admin Authentication:** A separate, secure login for administrators.

## Technology Stack

- **Backend:** PHP
- **Database:** MySQL
- **Frontend:**
    - HTML5
    - CSS3
    - JavaScript (ES6)
    - [Bootstrap 5](https://getbootstrap.com/) - For responsive design and UI components.
    - [Font Awesome](https://fontawesome.com/) - For icons.
    - [Google Fonts](https://fonts.google.com/) - For typography.
- **APIs:** RESTful API endpoints for cart and wishlist operations.

## Getting Started

### Prerequisites
- A web server with PHP version 7.4 or higher.
- A MySQL database server.
- A web browser.

### Installation Guide

1.  **Download or Clone:**
    ```bash
    git clone https://github.com/your-username/cart-api.git
    ```
2.  **Database Configuration:**
    - Locate the `db_connect.php` file.
    - Update the database credentials (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) to match your local environment.
3.  **Database Seeding:**
    - In your web browser, navigate to the `setup_database.php` script. For example: `http://localhost/cart-api/setup_database.php`.
    - This script will create all the necessary database tables and populate them with some sample data to get you started.
4.  **Admin Panel Access:**
    - Navigate to the `/admin` directory in your browser (e.g., `http://localhost/cart-api/admin`).
    - Log in using the default administrator credentials:
        - **Username:** `admin`
        - **Password:** `password`

## Project File Structure

```
/
├── admin/                # Admin panel files
│   ├── add_product.php
│   ├── orders.php
│   └── ...
├── assets/               # CSS, JS, and other assets
│   └── css/
│       └── style.css
├── uploads/              # Directory for product image uploads
├── about.php
├── cart-api.php          # API endpoint for cart operations
├── checkout.php
├── config.php
├── db_connect.php
├── index.php             # Main landing page
├── products.php
├── setup_database.php    # Database setup script
├── README.md
└── ...                   # Other PHP files
```

## API Endpoints

The application uses internal APIs for a more dynamic user experience:

- `POST /cart-api.php`: Manages all shopping cart operations (add, update, remove items).
- `POST /wishlist-api.php`: Manages the user's wishlist (add, remove items).
- `GET /search_suggestions.php`: Provides product suggestions for the search bar.

## Contributing

Contributions are welcome! If you have suggestions for improvements or new features, please feel free to open an issue or submit a pull request.

1.  Fork the Project.
2.  Create your Feature Branch (`git checkout -b feature/AmazingFeature`).
3.  Commit your Changes (`git commit -m 'Add some AmazingFeature'`).
4.  Push to the Branch (`git push origin feature/AmazingFeature`).
5.  Open a Pull Request.

## License

This project is licensed under the MIT License. See the `LICENSE` file for more details.

## Contact & Support

- **Email:** For any inquiries, please contact us at [help@rskworld.in](mailto:help@rskworld.in).
- **Website:** [http://rskworld.in/project/forms/login-form/](http://rskworld.in/project/forms/login-form/)

For full website development, please contact our team.