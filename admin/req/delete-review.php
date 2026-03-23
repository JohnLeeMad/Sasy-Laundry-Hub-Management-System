<?php
session_start();
include 'admin-auth-check.php';
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php'; // Add audit logger

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
    $review_id = intval($_POST['review_id']);

    try {
        // Get review details for audit logging
        $reviewDetails = 'Unknown Review';
        $getReviewStmt = $conn->prepare("
            SELECT r.rating, r.review_text, c.name as customer_name 
            FROM reviews r 
            LEFT JOIN users c ON r.customer_id = c.id 
            WHERE r.id = ?
        ");
        $getReviewStmt->bind_param("i", $review_id);
        $getReviewStmt->execute();
        $reviewResult = $getReviewStmt->get_result();

        if ($reviewResult->num_rows > 0) {
            $reviewRow = $reviewResult->fetch_assoc();
            $reviewDetails = $reviewRow['customer_name'] . "'s " . $reviewRow['rating'] . "-star review";

            // Truncate long review text for logging
            $reviewText = $reviewRow['review_text'];
            if (strlen($reviewText) > 100) {
                $reviewText = substr($reviewText, 0, 100) . '...';
            }
            $reviewDetails .= ' - "' . $reviewText . '"';
        }
        $getReviewStmt->close();

        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $review_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Review deleted successfully!";

            // AUDIT LOGGING - Log successful deletion
            if (file_exists(__DIR__ . '/audit-logger.php')) {
                $description = 'Deleted review: "' . $reviewDetails . '"';
                logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_review', $description);
            }
        } else {
            $_SESSION['error'] = "Failed to delete review.";

            // AUDIT LOGGING - Log deletion failure
            if (file_exists(__DIR__ . '/audit-logger.php')) {
                $errorDescription = 'Failed to delete review: "' . $reviewDetails . '"';
                logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_review_error', $errorDescription);
            }
        }

        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();

        // AUDIT LOGGING - Log general error
        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $errorDescription = 'Error deleting review ID ' . $review_id . ': ' . $e->getMessage();
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_review_error', $errorDescription);
        }
    }

    $conn->close();
} else {
    $_SESSION['error'] = "Invalid request.";

    // AUDIT LOGGING - Log invalid request
    if (file_exists(__DIR__ . '/audit-logger.php')) {
        $errorDescription = 'Invalid delete review request - no review ID provided';
        logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_review_error', $errorDescription);
    }
}

header("Location: ../reviews.php");
exit();
