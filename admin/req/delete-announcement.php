<?php
session_start();
require_once '../../config/db_conn.php';
require_once __DIR__ . '/audit-logger.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = 'Unauthorized access.';
    header('Location: ../prices-settings.php');
    exit;
}

if (isset($_GET['id'])) {
    $announcementId = intval($_GET['id']);

    try {
        $conn->begin_transaction();

        // Get announcement data for audit log before deletion
        $stmt = $conn->prepare("SELECT title, effective_date, announcement_type FROM price_announcements WHERE id = ?");
        $stmt->bind_param('i', $announcementId);
        $stmt->execute();
        $announcementData = $stmt->get_result()->fetch_assoc();

        if (!$announcementData) {
            throw new Exception('Announcement not found.');
        }

        // Delete announcement
        $stmt = $conn->prepare("DELETE FROM price_announcements WHERE id = ?");
        $stmt->bind_param('i', $announcementId);

        if ($stmt->execute()) {
            $conn->commit();

            // Audit logging
            $description = "Deleted announcement: '{$announcementData['title']}' (Effective: " .
                date('M j, Y', strtotime($announcementData['effective_date'])) . ", Type: {$announcementData['announcement_type']})";
            logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_announcement', $description);

            $_SESSION['success'] = 'Announcement deleted successfully!';
        } else {
            throw new Exception('Failed to delete announcement.');
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Error deleting announcement: ' . $e->getMessage();

        logActivity($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], 'delete_announcement_error', 'Failed to delete announcement: ' . $e->getMessage());
    }
} else {
    $_SESSION['error'] = 'Invalid announcement ID.';
}

header('Location: ../prices-settings.php');
exit;
