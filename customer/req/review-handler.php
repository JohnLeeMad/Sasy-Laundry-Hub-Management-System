<?php
session_start();
require_once '../../config/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_SESSION['customer_id'] ?? null;

    if (!$customer_id) {
        $_SESSION['error'] = "Please login to submit a review.";
        header("Location: ../index.php");
        exit();
    }

    if (isset($_POST['submit_review'])) {
        $laundry_list_id = intval($_POST['laundry_list_id'] ?? 0);
        $rating = intval($_POST['rating'] ?? 0);
        $review_text = trim($_POST['review_text'] ?? '');

        if ($laundry_list_id && $rating > 0 && $rating <= 5) {
            $check_sql = "SELECT id FROM laundry_lists WHERE id = ? AND customer_id = ? AND status = 'Claimed'";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $laundry_list_id, $customer_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                $check_review_sql = "SELECT id FROM reviews WHERE laundry_list_id = ? AND customer_id = ?";
                $check_review_stmt = $conn->prepare($check_review_sql);
                $check_review_stmt->bind_param("ii", $laundry_list_id, $customer_id);
                $check_review_stmt->execute();
                $existing_review = $check_review_stmt->get_result()->fetch_assoc();

                if ($existing_review) {
                    $sql = "UPDATE reviews SET rating = ?, review_text = ?, updated_at = NOW() 
                            WHERE laundry_list_id = ? AND customer_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isii", $rating, $review_text, $laundry_list_id, $customer_id);
                } else {
                    $sql = "INSERT INTO reviews (laundry_list_id, customer_id, rating, review_text, created_at) 
                            VALUES (?, ?, ?, ?, NOW())";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iiis", $laundry_list_id, $customer_id, $rating, $review_text);
                }

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Thank you for your review!";
                } else {
                    $_SESSION['error'] = "Failed to submit review. Please try again.";
                }
                $stmt->close();
                $check_review_stmt->close();
            } else {
                $_SESSION['error'] = "Invalid order or order not found.";
            }
            $check_stmt->close();
        } else {
            $_SESSION['error'] = "Please provide a valid rating (1-5 stars).";
        }
    } elseif (isset($_POST['dont_show_review'])) {
        $laundry_list_id = intval($_POST['laundry_list_id'] ?? 0);

        if ($laundry_list_id > 0) {
            $check_sql = "SELECT id FROM laundry_lists WHERE id = ? AND customer_id = ? AND status = 'Claimed'";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $laundry_list_id, $customer_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                $sql = "UPDATE laundry_lists SET review_dismissed = 1 WHERE id = ? AND customer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $laundry_list_id, $customer_id);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Got it! We won't ask for a review for this order again.";
                } else {
                    $_SESSION['error'] = "Failed to update your preference. Please try again.";
                }
                $stmt->close();
            } else {
                $_SESSION['error'] = "Invalid order.";
            }
            $check_stmt->close();
        } else {
            $_SESSION['error'] = "Invalid request.";
        }
    }

    $conn->close();
    header("Location: ../index.php");
    exit();
} else {
    header("Location: ../index.php");
    exit();
}
