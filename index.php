<?php
error_reporting(E_ALL);        // Ipakita lahat ng error, warnings, notices
ini_set('display_errors', 1);  // I-enable ang display ng errors sa browser
ini_set('display_startup_errors', 1); // Para kahit startup errors lumabas din

session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect logged-in users to their dashboard
$redirect_url = null;
if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in']) {
    $redirect_url = 'super-admin/dashboard.php';
} elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    $redirect_url = 'admin/dashboard.php';
} elseif (isset($_SESSION['staff_logged_in']) && $_SESSION['staff_logged_in']) {
    $redirect_url = 'staff/dashboard.php';
} elseif (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']) {
    $redirect_url = 'customer/index.php';
}

// If logged in, redirect using JavaScript with history replacement
if ($redirect_url) {
    echo '<script>window.location.replace("' . $redirect_url . '");</script>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="image.jpg" type="image/jpeg">
    <title>Sasy Laundry Hub</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/homepage.css">
</head>

<body>
    <?php
    // Database connection for fetching prices
    require_once 'config/db_conn.php';

    // Function to fetch laundry service prices
    function getLaundryPrices($conn)
    {
        $prices = [];
        $stmt = $conn->query("SELECT item_name, price FROM laundry_prices");
        if ($stmt) {
            while ($row = $stmt->fetch_assoc()) {
                $prices[$row['item_name']] = $row['price'];
            }
        }
        return $prices;
    }

    // Function to fetch supply product prices
    function getSupplyProductPrices($conn)
    {
        $products = [];
        $stmt = $conn->query("
            SELECT sp.name, sp.unit_price, sc.name as category_name
            FROM supply_products sp 
            LEFT JOIN supply_categories sc ON sp.category_id = sc.id 
            WHERE sp.is_active = 1 AND sp.name != 'Plastic Bag'
            ORDER BY sc.name, sp.name
        ");

        if ($stmt) {
            while ($row = $stmt->fetch_assoc()) {
                $category = $row['category_name'] ?? 'Uncategorized';
                $products[$category][] = [
                    'name' => $row['name'],
                    'unit_price' => $row['unit_price']
                ];
            }
        }
        return $products;
    }

    // Function to get specific product price range by category
    function getProductPriceRange($supplyProducts, $categoryKeyword)
    {
        $minPrice = PHP_FLOAT_MAX;
        $maxPrice = 0;
        $found = false;

        foreach ($supplyProducts as $category => $products) {
            if (stripos($category, $categoryKeyword) !== false) {
                foreach ($products as $product) {
                    $price = floatval($product['unit_price']);
                    if ($price > 0) {
                        $found = true;
                        $minPrice = min($minPrice, $price);
                        $maxPrice = max($maxPrice, $price);
                    }
                }
            }
        }

        if (!$found) {
            // Default prices if no products found
            if ($categoryKeyword === 'detergent') {
                return ['min' => 10.00, 'max' => 13.00];
            } elseif ($categoryKeyword === 'fabric') {
                return ['min' => 13.00, 'max' => 16.00];
            }
            return ['min' => 0, 'max' => 0];
        }

        return ['min' => $minPrice, 'max' => $maxPrice];
    }

    // Function to fetch customer reviews
    function getCustomerReviews($conn)
    {
        $reviews = [];
        $stmt = $conn->query("
            SELECT r.rating, r.review_text, r.created_at,
                c.name as customer_name
            FROM reviews r
            LEFT JOIN users c ON r.customer_id = c.id
            WHERE r.rating > 0 AND r.is_archived = FALSE
            ORDER BY r.created_at DESC
            LIMIT 12
        ");

        if ($stmt && $stmt->num_rows > 0) {
            while ($row = $stmt->fetch_assoc()) {
                $reviews[] = $row;
            }
        }
        return $reviews;
    }

    // Fetch current prices
    $prices = getLaundryPrices($conn);
    $supplyProducts = getSupplyProductPrices($conn);
    $customerReviews = getCustomerReviews($conn);

    // Calculate service prices based on database values
    $wash_price = $prices['wash_per_round'] ?? 70.00;
    $dry_price = $prices['dry_per_round'] ?? 70.00;
    $wash_dry_price = $wash_price + $dry_price;

    // Get actual supply product price ranges
    $detergent_prices = getProductPriceRange($supplyProducts, 'detergent');
    $fabcon_prices = getProductPriceRange($supplyProducts, 'fabric');

    // Calculate combo prices using actual supply product prices
    $wash_dry_detergent_min = $wash_dry_price + $detergent_prices['min'];
    $wash_dry_detergent_max = $wash_dry_price + $detergent_prices['max'];

    $wash_dry_fabcon_min = $wash_dry_price + $fabcon_prices['min'];
    $wash_dry_fabcon_max = $wash_dry_price + $fabcon_prices['max'];

    $wash_dry_detergent_fabcon_min = $wash_dry_price + $detergent_prices['min'] + $fabcon_prices['min'];
    $wash_dry_detergent_fabcon_max = $wash_dry_price + $detergent_prices['max'] + $fabcon_prices['max'];

    // Get folding service price
    $folding_price = $prices['folding_service'] ?? 0;
    $folding_display = ($folding_price == 0) ? 'FREE' : '₱' . number_format($folding_price, 2);
    ?>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-xl navbar-light fixed-top">
        <div class="container">
            <div class="navbar-brand">
                <img src="logo-removebg.png" alt="Sasy Laundry Hub Logo" class="navbar-logo">
                <span>Sasy Laundry Hub</span>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#reviews">Reviews</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faq">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a href="auth/unified-login.php" class="btn btn-login">Login / Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <!-- Animated Bubbles -->
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1>Laundry Made Easy with Our Laundry Management System</h1>
                    <p>From pre-listing to real-time updates and flexible add-ons, Sasy Laundry Hub gives you more control over your laundry.</p>
                    <div class="mt-4">
                        <a href="auth/customer-register.php" class="btn btn-cta">Get Started</a>
                        <a href="#services" class="btn btn-secondary-cta">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6 hero-image">
                    <div class="hero-carousel">
                        <!-- TO CHANGE IMAGE ORDER: Just reorder these divs! -->
                        <!-- Image 1 (will show first) -->
                        <div class="carousel-slide active">
                            <img src="assets/images/washing-machines-1.jpg" alt="Washing Machines">
                        </div>
                        <!-- Image 2 -->
                        <div class="carousel-slide">
                            <img src="assets/images/shop-interior-1.jpg" alt="Sasy Laundry Hub Interior">
                        </div>
                        <!-- Image 3 -->
                        <div class="carousel-slide">
                            <img src="assets/images/shop-interior-2.jpg" alt="Sasy Laundry Hub Interior">
                        </div>

                        <!-- Carousel Navigation Dots -->
                        <div class="carousel-dots">
                            <span class="dot active" data-slide="0"></span>
                            <span class="dot" data-slide="1"></span>
                            <span class="dot" data-slide="2"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="services">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose Sasy Laundry Hub?</h2>
                <p>We provide exceptional laundry services with attention to detail</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h4>Pay by Round, Not by Weight</h4>
                        <p>Say goodbye to confusing weighing scales! At Sasy Laundry Hub, your laundry is priced by service rounds, making it simple, transparent, and budget-friendly.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-sliders-h"></i>
                        </div>
                        <h4>Flexible Add-Ons</h4>
                        <p>Need extra detergent, drying, or folding service? We give you the freedom to choose only what you needâ€”customizing your laundry experience your way.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-list-ol"></i>
                        </div>
                        <h4>Fast & Reliable Queue System</h4>
                        <p>No more waiting without updates. Our dynamic queue system keeps your laundry organized, efficient, and ready right on schedule.</p>
                    </div>
                </div>
            </div>

            <!-- New Additional Services Section -->
            <div class="section-title mt-5">
                <h2>Enhanced Customer Experience</h2>
                <p>Modern features designed for your convenience</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4>Smart Pre-Listing</h4>
                        <p>Save time at the shop by pre-listing your laundry online. Just drop off, and we'll take care of the rest!</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4>Track Your Laundry Anytime</h4>
                        <p>Check your order status in real-time and know exactly when your clothes are ready for pickup.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h4>Customer Chat Support</h4>
                        <p>Got a question or special request? Message us directly through our system for quick assistance.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Pricing Section -->
    <section class="pricing-section" id="pricing">
        <div class="container">
            <div class="section-title">
                <h2>Our Pricing</h2>
                <p>Transparent and affordable laundry services</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="price-card">
                        <div class="price-header">
                            <h3>Service Rates</h3>
                        </div>
                        <ul class="price-list">
                            <li>
                                <span class="service-name">Wash (per round)</span>
                                <span class="service-price">₱<?php echo number_format($wash_price, 2); ?></span>
                            </li>
                            <li>
                                <span class="service-name">Dry (per round)</span>
                                <span class="service-price">₱<?php echo number_format($dry_price, 2); ?></span>
                            </li>
                            <li>
                                <span class="service-name">Wash + Dry + Detergent</span>
                                <span class="service-price">₱<?php echo number_format($wash_dry_detergent_min, 2); ?> - ₱<?php echo number_format($wash_dry_detergent_max, 2); ?>*</span>
                            </li>
                            <li>
                                <span class="service-name">Wash + Dry + Fabric Conditioner</span>
                                <span class="service-price">₱<?php echo number_format($wash_dry_fabcon_min, 2); ?> - ₱<?php echo number_format($wash_dry_fabcon_max, 2); ?>*</span>
                            </li>
                            <li>
                                <span class="service-name">Wash + Dry + Detergent + Fabric Conditioner</span>
                                <span class="service-price">₱<?php echo number_format($wash_dry_detergent_fabcon_min, 2); ?> - ₱<?php echo number_format($wash_dry_detergent_fabcon_max, 2); ?>*</span>
                            </li>
                            <li>
                                <span class="service-name">Folding Service</span>
                                <span class="service-price"><?php echo $folding_display; ?></span>
                            </li>
                        </ul>
                        <div class="price-note">
                            <p><strong>*Note:</strong> Final price depends on detergent and fabric conditioner brand selection. Additional add-ons (bleach, multiple rounds) may apply.</p>
                            <?php if (!empty($supplyProducts)): ?>
                                <p class="mt-2 small">
                                    <strong>Available Brands:</strong><br>
                                    <?php
                                    $brands = [];
                                    foreach ($supplyProducts as $category => $products) {
                                        foreach ($products as $product) {
                                            if (stripos($category, 'detergent') !== false || stripos($category, 'fabric') !== false) {
                                                $brands[] = $product['name'];
                                            }
                                        }
                                    }
                                    if (!empty($brands)) {
                                        echo implode(', ', array_slice($brands, 0, 5)) . (count($brands) > 5 ? ' and more...' : '');
                                    }
                                    ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="reviews-section" id="reviews">
        <div class="container">
            <div class="section-title">
                <h2>What Our Customers Say</h2>
                <p>Read testimonials from our satisfied customers</p>
            </div>

            <?php if (empty($customerReviews)): ?>
                <!-- Placeholder reviews when no reviews exist -->
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="review-card">
                            <div class="review-header">
                                <div class="review-avatar">?</div>
                                <div class="review-info">
                                    <h5>Be Our First Reviewer!</h5>
                                    <div class="review-stars">
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="review-text">"We're excited to serve you! Your feedback will help us improve and help others discover our service."</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Reviews Carousel -->
                <div class="reviews-carousel-container">
                    <?php
                    // Function to mask customer name
                    function maskCustomerName($name)
                    {
                        $name = trim($name);
                        if (empty($name)) {
                            return "Anonymous";
                        }

                        // Split name into parts (first name, last name, etc.)
                        $nameParts = explode(' ', $name);
                        $maskedParts = [];

                        foreach ($nameParts as $part) {
                            $part = trim($part);
                            if (empty($part)) continue;

                            $length = strlen($part);
                            if ($length <= 2) {
                                // Very short names, just show first letter
                                $maskedParts[] = substr($part, 0, 1) . '*';
                            } else {
                                // Show first letter, mask middle, show last letter
                                $firstChar = substr($part, 0, 1);
                                $lastChar = substr($part, -1);
                                $middleLength = $length - 2;
                                $maskedParts[] = $firstChar . str_repeat('*', $middleLength) . $lastChar;
                            }
                        }

                        return implode(' ', $maskedParts);
                    }

                    // Group reviews into pages of 3
                    $reviewPages = array_chunk($customerReviews, 3);
                    foreach ($reviewPages as $pageIndex => $pageReviews):
                    ?>
                        <div class="reviews-page <?php echo $pageIndex === 0 ? 'active' : ''; ?>">
                            <div class="row g-4">
                                <?php foreach ($pageReviews as $review):
                                    // Get initials for avatar
                                    $nameParts = explode(' ', $review['customer_name']);
                                    $initials = '';
                                    foreach ($nameParts as $part) {
                                        if (!empty($part)) {
                                            $initials .= strtoupper(substr($part, 0, 1));
                                            if (strlen($initials) >= 2) break;
                                        }
                                    }
                                    if (strlen($initials) < 2 && !empty($review['customer_name'])) {
                                        $initials = strtoupper(substr($review['customer_name'], 0, 2));
                                    }

                                    // Mask customer name
                                    $maskedName = maskCustomerName($review['customer_name']);

                                    // Truncate review text if too long
                                    $reviewText = $review['review_text'];
                                    if (strlen($reviewText) > 150) {
                                        $reviewText = substr($reviewText, 0, 150) . '...';
                                    }
                                ?>
                                    <div class="col-md-4">
                                        <div class="review-card">
                                            <div class="review-header">
                                                <div class="review-avatar"><?php echo htmlspecialchars($initials); ?></div>
                                                <div class="review-info">
                                                    <h5><?php echo htmlspecialchars($maskedName); ?></h5>
                                                    <div class="review-stars">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : ' text-muted'; ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <p class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></p>
                                                </div>
                                            </div>
                                            <p class="review-text">"<?php echo htmlspecialchars($reviewText); ?>"</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (count($reviewPages) > 1): ?>
                        <!-- Carousel Navigation -->
                        <div class="reviews-carousel-nav">
                            <?php foreach ($reviewPages as $pageIndex => $pageReviews): ?>
                                <span class="reviews-dot <?php echo $pageIndex === 0 ? 'active' : ''; ?>" data-page="<?php echo $pageIndex; ?>"></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section" id="faq">
        <div class="container">
            <div class="section-title">
                <h2>Frequently Asked Questions</h2>
                <p>Find answers to common questions about our services</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    What are your business hours?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We are open daily from 7:00 AM to 6:00 PM. Laundry drop-offs are accepted anytime during business hours.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    How long does it take to process laundry?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    The turnaround time may vary, but in most cases, your laundry can be processed within the same day, especially if there are fewer customers.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We currently accept cash payments only.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Can I pre-list my laundry order online?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes! You can register for an account and pre-list your laundry order online. This allows us to prepare in advance and makes the drop-off process quicker.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    Do you offer pickup and delivery services?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    At the moment, we do not provide pickup and delivery services. Our laundry shop is walk-in only.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Contact Section -->
    <section class="contact-section" id="contact">
        <div class="container">
            <div class="section-title">
                <h2>Get In Touch</h2>
                <p>We'd love to hear from you</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="contact-info">
                        <h3>Contact Information</h3>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h5>Phone</h5>
                                <p>09551375331</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h5>Business Hours</h5>
                                <p>Daily: 7:00 AM - 6:00 PM</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h5>Address</h5>
                                <p>Bayabas St, Brgy. Poblacion, General Tinio</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!3m2!1sen!2sph!4v1759807693815!5m2!1sen!2sph!6m8!1m7!1sDoVdTkp8GrsRGa85-rOiog!2m2!1d15.34586595368735!2d121.053599990886!3f139.8324450757522!4f-5.375146051454806!5f0.7820865974627469"
                            width="100%"
                            height="500"
                            style="border:0; border-radius: 8px;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row footer-content">
                <div class="col-lg-4 mb-3">
                    <h5>Sasy Laundry Hub</h5>
                    <p>At Sasy Laundry Hub, laundry is priced by wash rounds, transparent, consistent, and hassle-free.</p>
                </div>
                <div class="col-lg-4 mb-3">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#reviews">Reviews</a></li>
                        <li><a href="#faq">FAQ</a></li>
                        <li><a href="auth/unified-login.php"> Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-3">
                    <h5>Contact Us</h5>
                    <ul class="footer-links">
                        <li><i class="fas fa-phone me-2"></i> 09551375331</li>
                        <li><i class="fas fa-clock me-2"></i> 7:00 AM - 6:00 PM </li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> Bayabas St, Brgy. Poblacion, General Tinio</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p> 2025 Sasy Laundry Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Hero Carousel Functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.dot');
        const totalSlides = slides.length;

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));

            slides[index].classList.add('active');
            dots[index].classList.add('active');
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            showSlide(currentSlide);
        }

        // Auto-advance every 5 seconds
        setInterval(nextSlide, 5000);

        // Dot click navigation
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                showSlide(currentSlide);
            });
        });

        // Reviews Carousel Functionality
        let currentReviewPage = 0;
        const reviewPages = document.querySelectorAll('.reviews-page');
        const reviewDots = document.querySelectorAll('.reviews-dot');
        const totalReviewPages = reviewPages.length;

        if (totalReviewPages > 0) {
            function showReviewPage(index) {
                reviewPages.forEach(page => page.classList.remove('active'));
                reviewDots.forEach(dot => dot.classList.remove('active'));

                reviewPages[index].classList.add('active');
                if (reviewDots[index]) {
                    reviewDots[index].classList.add('active');
                }
            }

            function nextReviewPage() {
                currentReviewPage = (currentReviewPage + 1) % totalReviewPages;
                showReviewPage(currentReviewPage);
            }

            // Auto-advance every 7 seconds
            if (totalReviewPages > 1) {
                setInterval(nextReviewPage, 7000);
            }

            // Dot click navigation
            reviewDots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    currentReviewPage = index;
                    showReviewPage(currentReviewPage);
                });
            });
        }

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const navbarHeight = document.querySelector('.navbar').offsetHeight;
                    const targetPosition = target.offsetTop - navbarHeight;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Active nav link highlighting
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

        const observerOptions = {
            root: null,
            rootMargin: '-50% 0px -50% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${entry.target.id}`) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        }, observerOptions);

        sections.forEach(section => {
            observer.observe(section);
        });

        // On load, set home active
        document.querySelector('.nav-link[href="#home"]').classList.add('active');
    </script>
</body>

</html>