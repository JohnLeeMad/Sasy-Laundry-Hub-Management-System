<?php
require_once '../config/db_conn.php';

$admin_id = $_SESSION['admin_id'];

$sql = "SELECT id, name, email FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($admin = $result->fetch_assoc()) {
    $_SESSION['admin_details'] = $admin;
} else {
    echo "Admin details not found.";
}
?>