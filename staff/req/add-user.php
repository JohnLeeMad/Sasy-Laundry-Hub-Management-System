<?php
require_once '../../config/db_conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $contactNum = $_POST['contact_num'];
    $email = $_POST['email'];
    $type = $_POST['type'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

    try {
        // Check if the phone number already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE contact_num = ?");
        $stmt->bind_param('s', $contactNum);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['error'] = 'A user with this phone number already exists.';
            header('Location: ../users.php');
            exit;
        }

        // Insert the new user
        $stmt = $conn->prepare("INSERT INTO users (name, contact_num, email, type, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $name, $contactNum, $email, $type, $password);
        $stmt->execute();

        $_SESSION['success'] = 'User added successfully.';
        header('Location: ../users.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error adding user: ' . $e->getMessage();
        header('Location: ../users.php');
        exit;
    }
}
?>