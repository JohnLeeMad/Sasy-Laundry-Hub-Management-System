<?php

require_once '../config/db_conn.php';

$staff_id = $_SESSION['staff_id'];

$sql = "SELECT id, name, email FROM staffs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($staff = $result->fetch_assoc()) {
    $_SESSION['staff_details'] = $staff; // Store all admin details in session
} else {
    echo "Staff details not found.";
}
