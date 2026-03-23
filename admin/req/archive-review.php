<?php
session_start();
include 'admin-auth-check.php';
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id']) && isset($_POST['is_archived'])) {
    $review_id = intval($_POST['review_id']);
    $is_archived = $_POST['is_archived'] === '1';

    try {
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
        }
        $getReviewStmt->close();

        $stmt = $conn->prepare("UPDATE reviews SET is_archived = ? WHERE id = ?");
        $stmt->bind_param("ii", $is_archived, $review_id);

        if ($stmt->execute()) {
            $action = $is_archived ? 'archive_review' : 'unarchive_review';
            $message = $is_archived ? 'Review hidden from homepage successfully!' : 'Review shown on homepage successfully!';

            $_SESSION['success'] = $message;

            if (file_exists(__DIR__ . '/audit-logger.php')) {
                $description = ($is_archived ? 'Archived' : 'Unarchived') . ' review: "' . $reviewDetails . '"';
                logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], $action, $description);
            }
        } else {
            throw new Exception("Failed to update review status");
        }

        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();

        if (file_exists(__DIR__ . '/audit-logger.php')) {
            $errorDescription = 'Error ' . ($is_archived ? 'archiving' : 'unarchiving') . ' review ID ' . $review_id . ': ' . $e->getMessage();
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'archive_review_error', $errorDescription);
        }
    }

    $conn->close();
} else {
    $_SESSION['error'] = "Invalid request.";

    if (file_exists(__DIR__ . '/audit-logger.php')) {
        $errorDescription = 'Invalid toggle archive request - missing parameters';
        logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'archive_review_error', $errorDescription);
    }
}

header("Location: ../reviews.php");
exit();
?>