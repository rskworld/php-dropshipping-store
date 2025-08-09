<?php
$pageTitle = 'Contact | RSK Dropshipping Template';
$currentPage = 'contact';

require_once 'config.php'; // Include config to get dynamic settings

// Simple form handling
$successMsg = '';
$errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $first = htmlspecialchars(trim($_POST['first_name'] ?? ''));
    $last  = htmlspecialchars(trim($_POST['last_name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if (!$first || !$last || !$email || !$subject || !$message) {
        $errorMsg = 'Please fill in all required fields correctly.';
    } else {
        // Save to database instead of sending email
        try {
            require_once 'db_connect.php';
            $stmt = $pdo->prepare("INSERT INTO feedback (first_name, last_name, email, subject, message) VALUES (:first, :last, :email, :subject, :message)");
            $stmt->execute([
                'first' => $first,
                'last' => $last,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ]);
            $successMsg = 'Thank you! Your message has been sent.';
            // Clear form fields
            $first = $last = $email = $subject = $message = '';
        } catch (PDOException $e) {
            $errorMsg = 'Sorry, something went wrong while sending your message: ' . $e->getMessage();
        }
    }
}

require 'header.php';
?>

<div class="container page-content" style="margin-top: 100px; padding-bottom: 120px;">
    <h1 class="section-title">Contact Us</h1>

    <?php if ($successMsg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($successMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif ($errorMsg): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($errorMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card feature-card">
                <div class="card-body p-5">
                    <form method="POST" action="contact.php" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" placeholder="Enter your first name" value="<?= isset($first) ? htmlspecialchars($first) : '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" placeholder="Enter your last name" value="<?= isset($last) ? htmlspecialchars($last) : '' ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="your.email@example.com" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select name="subject" class="form-select" required>
                                <option value="">Choose...</option>
                                <option <?= (isset($subject) && $subject === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                <option <?= (isset($subject) && $subject === 'Technical Support') ? 'selected' : ''; ?>>Technical Support</option>
                                <option <?= (isset($subject) && $subject === 'Supplier Partnership') ? 'selected' : ''; ?>>Supplier Partnership</option>
                                <option <?= (isset($subject) && $subject === 'Business Opportunity') ? 'selected' : ''; ?>>Business Opportunity</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="5" placeholder="Tell us how we can help you..." required><?= isset($message) ? htmlspecialchars($message) : '' ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>

            <div class="row mt-5 mb-4">
                <div class="col-md-4 text-center">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h5>Email</h5>
                    <p><?= htmlspecialchars(CONTACT_EMAIL) ?></p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h5>Phone</h5>
                    <p><?= htmlspecialchars(CONTACT_PHONE) ?></p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h5>Location</h5>
                    <p><?= htmlspecialchars(CONTACT_ADDRESS) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
