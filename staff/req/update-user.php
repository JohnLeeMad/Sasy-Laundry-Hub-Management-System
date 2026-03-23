<?php
require_once '../../config/db_conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $contactNum = $_POST['contact_num'];
    $email = $_POST['email'];
    $type = $_POST['type'];

    try {
        $stmt = $conn->prepare("UPDATE users SET name = ?, contact_num = ?, email = ?, type = ? WHERE id = ?");
        $stmt->bind_param('ssssi', $name, $contactNum, $email, $type, $id);
        $stmt->execute();

        $_SESSION['success'] = 'User updated successfully.';
        header('Location: ../customers.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error updating user: ' . $e->getMessage();
        header('Location: ../customers.php');
        exit;
    }
}
?>