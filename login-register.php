<?php
session_start();
require_once 'db_connect.php';

$pageTitle = 'Login or Register | RSK Dropshipping Template';
$currentPage = 'login-register';

$loginError = '';
$registerError = '';
$registerSuccess = '';

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($name) || empty($email) || empty($password) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerError = 'Please fill in all fields correctly.';
    } elseif ($password !== $password_confirm) {
        $registerError = 'Passwords do not match.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $registerError = 'Email address is already registered.';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)");
                $stmt->execute(['name' => $name, 'email' => $email, 'password_hash' => $password_hash]);
                $registerSuccess = 'Registration successful! You can now log in.';
            }
        } catch (PDOException $e) {
            $registerError = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loginError = 'Please enter a valid email and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password_hash FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                header('Location: my_account.php');
                exit;
            } else {
                $loginError = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $loginError = 'Database error: ' . $e->getMessage();
        }
    }
}

require 'header.php';
?>

<div class="container page-content" style="margin-top: 100px;">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card feature-card">
                <div class="card-body p-5">
                    <ul class="nav nav-pills nav-fill mb-4" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-login-tab" data-bs-toggle="pill" data-bs-target="#pills-login" type="button" role="tab" aria-controls="pills-login" aria-selected="true">Login</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-register-tab" data-bs-toggle="pill" data-bs-target="#pills-register" type="button" role="tab" aria-controls="pills-register" aria-selected="false">Register</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-login" role="tabpanel" aria-labelledby="pills-login-tab">
                            <h3 class="text-center mb-4">Login to Your Account</h3>
                            <?php if ($loginError): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($loginError) ?></div>
                            <?php endif; ?>
                            <form method="POST" action="login-register.php">
                                <input type="hidden" name="login" value="1">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="pills-register" role="tabpanel" aria-labelledby="pills-register-tab">
                            <h3 class="text-center mb-4">Create an Account</h3>
                            <?php if ($registerError): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($registerError) ?></div>
                            <?php endif; ?>
                            <?php if ($registerSuccess): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($registerSuccess) ?></div>
                            <?php endif; ?>
                            <form method="POST" action="login-register.php">
                                <input type="hidden" name="register" value="1">
                                <div class="mb-3">
                                    <label for="register_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="register_name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="register_email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="register_email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="register_password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="register_password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirm" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Register</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
