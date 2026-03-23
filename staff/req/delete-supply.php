<?php
require_once '../../config/db_conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Check if the supply exists
    $stmt = $conn->prepare("SELECT * FROM supply_list WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Supply not found.';
        header('Location: ../supply-list.php');
        exit;
    }

    // Delete the supply
    $stmt = $conn->prepare("DELETE FROM supply_list WHERE id = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Supply deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete supply.';
    }

    header('Location: ../supply-list.php');
    exit;
} else {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: ../supply-list.php');
    exit;
}
