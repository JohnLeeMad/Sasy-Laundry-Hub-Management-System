<?php
function getChatRoom($conn, $customer_id)
{
    $stmt = $conn->prepare("SELECT * FROM chat_rooms WHERE customer_id = ? AND is_active = 1");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function createChatRoom($conn, $customer_id, $customer_name, $customer_email)
{
    $stmt = $conn->prepare("INSERT INTO chat_rooms (customer_id, customer_name, customer_email) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $customer_id, $customer_name, $customer_email);
    $stmt->execute();
    return $conn->insert_id;
}

function getChatMessages($conn, $room_id)
{
    $stmt = $conn->prepare("SELECT * FROM chat_messages WHERE room_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function sendMessage($conn, $room_id, $sender_id, $sender_type, $sender_name, $message, $message_type = 'text', $image_path = null)
{
    $stmt = $conn->prepare("INSERT INTO chat_messages (room_id, sender_id, sender_type, sender_name, message, message_type, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssss", $room_id, $sender_id, $sender_type, $sender_name, $message, $message_type, $image_path);

    if ($stmt->execute()) {
        $update_stmt = $conn->prepare("UPDATE chat_rooms SET last_message_at = NOW() WHERE id = ?");
        $update_stmt->bind_param("i", $room_id);
        $update_stmt->execute();
        return true;
    }
    return false;
}

function uploadChatImage($file, $room_id)
{
    $upload_dir = '../assets/uploads/chat-images/';

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, and GIF allowed.'];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File too large. Maximum 5MB allowed.'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'chat_' . $room_id . '_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'path' => '../assets/uploads/chat-images/' . $filename];
    }

    return ['success' => false, 'error' => 'Failed to upload image.'];
}

function getActiveChats($conn)
{
    $stmt = $conn->prepare("
        SELECT cr.*, 
               c.contact_num as customer_phone,
               MAX(cm.message) as last_message,
               MAX(cm.created_at) as last_message_time,
               COUNT(CASE WHEN cm.is_read = 0 AND cm.sender_type = 'customer' THEN 1 END) as unread_count
        FROM chat_rooms cr
        LEFT JOIN users c ON cr.customer_id = c.id
        LEFT JOIN (
            SELECT room_id, message, created_at, is_read, sender_type
            FROM chat_messages
            WHERE id IN (
                SELECT MAX(id)
                FROM chat_messages
                GROUP BY room_id
            )
        ) cm ON cr.id = cm.room_id
        WHERE cr.is_active = 1
        GROUP BY cr.id, cr.customer_id, cr.customer_name, cr.customer_email, cr.is_active, cr.created_at, cr.last_message_at, c.contact_num
        ORDER BY cr.last_message_at DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getMessageSeenTime($conn, $message_id)
{
    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(read_at, '%h:%i %p') as seen_time 
        FROM chat_messages 
        WHERE id = ? AND is_read = 1 AND read_at IS NOT NULL
    ");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['seen_time'] : null;
}

function markMessagesAsRead($conn, $room_id, $reader_type = null)
{
    if ($reader_type) {
        if (in_array($reader_type, ['admin', 'staff'])) {
            $stmt = $conn->prepare("
                UPDATE chat_messages 
                SET is_read = 1, read_at = NOW() 
                WHERE room_id = ? AND sender_type = 'customer' AND is_read = 0
            ");
        } else {
            $stmt = $conn->prepare("
                UPDATE chat_messages 
                SET is_read = 1, read_at = NOW() 
                WHERE room_id = ? AND sender_type IN ('admin', 'staff') AND is_read = 0
            ");
        }
        $stmt->bind_param("i", $room_id);
    } else {
        $stmt = $conn->prepare("
            UPDATE chat_messages 
            SET is_read = 1, read_at = NOW() 
            WHERE room_id = ? AND is_read = 0
        ");
        $stmt->bind_param("i", $room_id);
    }
    $stmt->execute();
}

function getUnreadCount($conn, $room_id, $for_admin_staff = false)
{
    if ($for_admin_staff) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM chat_messages WHERE room_id = ? AND sender_type = 'customer' AND is_read = 0");
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM chat_messages WHERE room_id = ? AND sender_type IN ('admin', 'staff') AND is_read = 0");
    }
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}

function getTotalUnreadCount($conn, $for_admin_staff = false)
{
    if ($for_admin_staff) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM chat_messages cm 
            JOIN chat_rooms cr ON cm.room_id = cr.id 
            WHERE cm.sender_type = 'customer' AND cm.is_read = 0 AND cr.is_active = 1
        ");
    } else {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM chat_messages cm 
            JOIN chat_rooms cr ON cm.room_id = cr.id 
            WHERE cm.sender_type IN ('admin', 'staff') AND cm.is_read = 0 AND cr.is_active = 1
        ");
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}
