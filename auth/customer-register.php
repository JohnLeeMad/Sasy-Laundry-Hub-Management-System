<?php
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect if already logged in
$redirect_url = null;
if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in']) {
    $redirect_url = '../super-admin/dashboard.php';
} elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    $redirect_url = '../admin/dashboard.php';
} elseif (isset($_SESSION['staff_logged_in']) && $_SESSION['staff_logged_in']) {
    $redirect_url = '../staff/dashboard.php';
} elseif (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']) {
    $redirect_url = '../customer/index.php';
}

if ($redirect_url) {
    echo '<script>window.location.replace("' . $redirect_url . '");</script>';
    exit();
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include the renderAlert function
function renderAlert($type, $message)
{
    if (!empty($message)) {
        $icon = $type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        $bgColor = $type === 'success' ? 'bg-success' : 'bg-danger';
        echo '
        <div class="alert ' . $bgColor . ' text-white alert-dismissible fade show mt-3 animate__animated animate__fadeInDown" role="alert" style="border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <i class="' . $icon . ' me-2"></i>
            <span>' . htmlspecialchars($message) . '</span>
        </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Customer Registration</title>
    <link rel="icon" href="../image.jpg" type="image/jpeg">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">

    <link href="../assets/css/admin-login.css" rel="stylesheet">
    <link href="../assets/css/customer-reg.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="login-card card">
                    <div class="card-header">
                        <a href="/">
                            <img src="../logo.jpg" alt="Laundry Management System Logo" class="img-fluid mb-3" style="max-height: 90px;">
                        </a>
                        <h3 class="fw-bold text-primary" style="color: var(--primary-color) !important;">Customer Registration</h3>
                        <p class="text-muted mb-0">Create your account to get started</p>
                    </div>

                    <div class="card-body p-4">
                        <!-- Alert section for success or error messages -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <?php renderAlert('success', $_SESSION['success']); ?>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <?php renderAlert('error', $_SESSION['error']); ?>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <form method="POST" action="../auth/customer-register-process.php" novalidate id="registrationForm">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                            <div class="mb-4">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input id="name"
                                        type="text"
                                        name="name"
                                        value="<?php echo htmlspecialchars($_SESSION['old']['name'] ?? ''); ?>"
                                        required
                                        class="form-control"
                                        autofocus
                                        placeholder="Enter your full name">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input id="email"
                                        type="email"
                                        name="email"
                                        value="<?php echo htmlspecialchars($_SESSION['old']['email'] ?? ''); ?>"
                                        required
                                        class="form-control"
                                        autocomplete="username"
                                        placeholder="Enter your email address">
                                </div>
                                <div class="form-text">We'll send a verification link to this email address.</div>
                            </div>

                            <div class="mb-4">
                                <label for="contact_num" class="form-label">Contact Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input id="contact_num"
                                        type="tel"
                                        name="contact_num"
                                        value="<?php echo htmlspecialchars($_SESSION['old']['contact_num'] ?? ''); ?>"
                                        required
                                        class="form-control"
                                        placeholder="Enter your contact number">
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="termsCheckbox" name="terms" required readonly onclick="openTermsModal()">
                                    <label class="form-check-label" for="termsCheckbox">
                                        I agree to the <a href="#" onclick="openTermsModal()">Terms of Service</a> <span class="text-danger">*</span>
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="privacyCheckbox" name="privacy_policy" required readonly onclick="openPrivacyModal()">
                                    <label class="form-check-label" for="privacyCheckbox">
                                        I agree to the <a href="#" onclick="openPrivacyModal()">Privacy Policy</a> <span class="text-danger">*</span>
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="login-footer p-3">
                        <p class="mb-2 text-center">Already have an account? <span class="forgot-password"><a href="unified-login.php">Login here</a></span></p>
                        <p class="mb-0"><?php echo date('Y'); ?> Laundry Management System</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms of Service Modal -->
    <div class="modal fade terms-modal" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="text-muted small mb-3">Last updated: <?php echo date('F j, Y'); ?></p>

                    <h5>1. Acceptance of Terms</h5>
                    <p>By using our laundry shop system and services, you agree to these Terms of Service. If you do not agree, please do not proceed with placing an order.</p>

                    <h5>2. Description of Service</h5>
                    <p>Our laundry shop provides services including pre-listing, order placement, washing, drying, folding, order tracking, and payment processing.</p>

                    <h5>3. Payment Terms</h5>
                    <ul>
                        <li>We only accept <strong>cash payments</strong>.</li>
                        <li>If clothes are brought to the shop, a <strong>partial payment</strong> is required upon order placement.</li>
                        <li>The <strong>remaining balance</strong> must be fully settled before claiming the finished laundry.</li>
                    </ul>

                    <h5>4. Cancellation & Refund Policy</h5>
                    <ul>
                        <li>Pre-listed orders may be cancelled if they have not yet been accepted at the shop.</li>
                        <li>Orders already at the shop may only be cancelled and refunded <strong>if washing has not yet started</strong>.</li>
                        <li>Once washing begins, cancellations and refunds are no longer allowed.</li>
                    </ul>

                    <h5>5. Liability for Lost or Damaged Clothes</h5>
                    <ul>
                        <li>The shop is liable for damages caused by the washing process (e.g., clothes stuck in the machine).</li>
                        <li>Our staff will warn customers if clothes appear too weak or fragile before accepting them.</li>
                        <li>Customers have the option to request clothes be <strong>counted and listed</strong> during walk-in orders.</li>
                        <li>Customers also have the option to input the number of clothes during pre-listing.</li>
                        <li>If clothes were not listed, or the listing is correct after checking and the customer insist that something is missing, the shop will not be held responsible for missing items.</li>
                    </ul>

                    <h5>6. Customer Preferences</h5>
                    <p>Customers may choose additional services such as folding and dryer rounds. The shop is not liable for shrinkage, fabric damage, or changes caused by customer-selected preferences.</p>

                    <h5>7. Order Pickup</h5>
                    <p>Finished laundry must be claimed within <strong>30 days</strong>. Unclaimed items beyond this period may be disposed of without further notice.</p>

                    <h5>8. Limitation of Liability</h5>
                    <p>We are not responsible for indirect, incidental, or consequential damages arising from the use of our services.</p>

                    <h5>9. Changes to Terms</h5>
                    <p>We reserve the right to update these terms at any time. Continued use of our services after changes means you accept the new terms.</p>

                    <div class="terms-checkbox">
                        <input type="checkbox" id="modalTermsCheckbox" disabled>
                        <label for="modalTermsCheckbox">I have read and agree to the Terms of Service</label>
                    </div>
                    <div class="scroll-indicator" id="termsScrollIndicator">
                        <i class="fas fa-arrow-down"></i> Scroll to bottom to accept
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" disabled>Close</button>
                    <button type="button" class="btn btn-accept btn-sm" id="acceptTermsBtn" disabled>Accept</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Privacy Policy Modal -->
    <div class="modal fade terms-modal" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="text-muted small mb-3">Last updated: <?php echo date('F j, Y'); ?></p>

                    <h5>1. Information We Collect</h5>
                    <p>We collect the following information when you register, place an order, or use our system:</p>
                    <ul>
                        <li>Name and contact number</li>
                        <li>Email address (for registered accounts)</li>
                        <li>Laundry order details (wash rounds, detergent scoops, folding, dryer, remarks)</li>
                        <li>Payment details (amount tendered, change, stored balance, total price, payment status)</li>
                    </ul>

                    <h5>2. How We Use Your Information</h5>
                    <p>Your information is used to:</p>
                    <ul>
                        <li>Process and manage your laundry orders</li>
                        <li>Notify you when your laundry is ready for pickup</li>
                        <li>Maintain stored balance (if you choose to keep change as credit)</li>
                        <li>Improve record-keeping and customer service</li>
                    </ul>

                    <h5>3. Data Sharing</h5>
                    <p>We do not sell or share your personal information with third parties. Your information is only accessible by our laundry shop staff for service purposes.</p>

                    <h5>4. Data Security</h5>
                    <p>We take reasonable steps to protect your data stored in our system. However, please note that no system is 100% secure, and we cannot guarantee absolute protection against all risks.</p>

                    <h5>5. Your Rights</h5>
                    <p>You may:</p>
                    <ul>
                        <li>Update your account password at any time</li>
                        <li>Request account deletion by contacting the shop</li>
                        <li>Ask staff for clarification about your laundry records or stored balance</li>
                    </ul>

                    <h5>6. Cookies and Tracking</h5>
                    <p>Our system does not use cookies or third-party tracking. Information is only stored directly in our database to process your orders.</p>

                    <h5>7. Changes to This Policy</h5>
                    <p>We may update this Privacy Policy from time to time. Any changes will be reflected here with the updated date.</p>

                    <h5>8. Contact Us</h5>
                    <p>If you have questions about your privacy, you can:</p>
                    <ul>
                        <li>Contact us at <strong>0955 137 5331</strong></li>
                        <li>Send us a message through the in-system customer chat when logged in</li>
                    </ul>

                    <div class="terms-checkbox">
                        <input type="checkbox" id="modalPrivacyCheckbox" disabled>
                        <label for="modalPrivacyCheckbox">I have read and agree to the Privacy Policy</label>
                    </div>
                    <div class="scroll-indicator" id="privacyScrollIndicator">
                        <i class="fas fa-arrow-down"></i> Scroll to bottom to accept
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" disabled>Close</button>
                    <button type="button" class="btn btn-accept btn-sm" id="acceptPrivacyBtn" disabled>Accept</button>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../assets/js/customer-reg.js"></script>

</body>

</html>