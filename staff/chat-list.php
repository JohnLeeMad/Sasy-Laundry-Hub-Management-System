<?php
session_start();
include 'req/staff-auth-check.php';

require_once '../config/db_conn.php';
require_once '../customer/req/chat-functions.php';

$role = $_SESSION['admin_logged_in'] ?? false ? 'admin' : ($_SESSION['staff_logged_in'] ?? false ? 'staff' : 'guest');

if (!in_array($role, ['admin', 'staff'])) {
    header('Location: ../auth/unified-login.php');
    exit();
}

function getActiveChatsList($conn)
{
    $stmt = $conn->prepare("
        SELECT cr.*, 
               c.contact_num as customer_phone,
               cm.message as last_message,
               cm.created_at as last_message_time,
               cm.sender_type as last_message_sender,
               cm.is_read as last_message_read,
               COUNT(CASE WHEN unread_cm.is_read = 0 AND unread_cm.sender_type = 'customer' THEN 1 END) as unread_count
        FROM chat_rooms cr
        LEFT JOIN users c ON cr.customer_id = c.id
        LEFT JOIN chat_messages cm ON cm.id = (
            SELECT MAX(id) 
            FROM chat_messages 
            WHERE room_id = cr.id
        )
        LEFT JOIN chat_messages unread_cm ON unread_cm.room_id = cr.id
        WHERE cr.is_active = 1
        GROUP BY cr.id, cr.customer_id, cr.customer_name, cr.customer_email, cr.is_active, cr.created_at, cr.last_message_at, c.contact_num, cm.message, cm.created_at, cm.sender_type, cm.is_read
        ORDER BY cr.last_message_at DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

$chats = getActiveChatsList($conn);
$total_unread = getTotalUnreadCount($conn, true);

$header = "Customer Chats";
$slot = '
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #644499; color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-comments me-2"></i>
                    Customer Conversations
                </h5>
                ' . ($total_unread > 0 ? '<span class="badge bg-danger">' . $total_unread . ' unread</span>' : '') . '
            </div>
            
            <div class="card-body p-0">';

if (empty($chats)) {
    $slot .= '
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No customer conversations yet</h5>
                    <p class="text-muted">Customer conversations will appear here when they start chatting</p>
                </div>';
} else {
    $slot .= '
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%">Customer</th>
                                <th>Contact Number</th>
                                <th>Last Message</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>';

    foreach ($chats as $chat) {
        $unread_count = $chat['unread_count'];
        $last_message = $chat['last_message'] ? htmlspecialchars(substr($chat['last_message'], 0, 50)) . '...' : 'No messages yet';
        $time_ago = $chat['last_message_time'] ? timeAgo($chat['last_message_time']) : 'Never';

        $status_badge = '';
        if ($chat['last_message']) {
            if ($unread_count > 0) {
                $status_badge = '<span class="badge bg-danger">' . $unread_count . ' new</span>';
            } elseif ($chat['last_message_sender'] === 'customer') {
                // Last message was from customer - check if admin/staff has read it
                if ($chat['last_message_read'] == 1) {
                    $status_badge = '<span class="badge bg-success">Read</span>';
                } else {
                    $status_badge = '<span class="badge bg-warning text-dark">Unread</span>';
                }
            } else {
                // Last message was from admin/staff - check if customer has read it
                if ($chat['last_message_read'] == 1) {
                    $status_badge = '<span class="badge bg-info">Seen</span>';
                } else {
                    $status_badge = '<span class="badge bg-secondary">Sent</span>';
                }
            }
        } else {
            $status_badge = '<span class="badge bg-light text-dark">No messages</span>';
        }

        $slot .= '
                            <tr class="' . ($unread_count > 0 ? 'table-warning' : '') . '">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">' . htmlspecialchars($chat['customer_name']) . '</h6>
                                            <small class="text-muted">Email: ' . $chat['customer_email'] . '</small>
                                        </div>
                                    </div>
                                </td>
                                <td>' . htmlspecialchars($chat['customer_phone']) . '</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;">
                                        ' . $last_message . '
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">' . $time_ago . '</small>
                                </td>
                                <td>' . $status_badge . '</td>
                                <td>
                                    <a href="chat-conversation.php?room_id=' . $chat['id'] . '" class="btn btn-sm btn-primary">
                                        <i class="fas fa-comments me-1"></i>
                                        View Chat
                                    </a>
                                </td>
                            </tr>';
    }

    $slot .= '
                        </tbody>
                    </table>
                </div>';
}

$slot .= '
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 0.875rem;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.btn-primary {
    background-color: #644499;
    border-color: #644499;
}

.btn-primary:hover {
    background-color: #644499;
    border-color: #644499;
    box-shadow: 0 5px 15px rgba(100, 68, 153, 0.4);
}

.badge {
    font-size: 0.75rem;
    font-weight: 500;
}

/* Status badge colors */
.badge.bg-danger {
    background-color: #dc3545 !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.badge.bg-success {
    background-color: #198754 !important;
}

.badge.bg-info {
    background-color: #0dcaf0 !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
}

.badge.bg-light {
    background-color: #f8f9fa !important;
    border: 1px solid #dee2e6;
    color: #6c757d !important;
}

/* Card and table styling */
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-header {
    background: #644499;
    color: white;
    border-radius: 10px 10px 0 0 !important;
}

.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    color: #644499;
    font-weight: 600;
}

.table td {
    border-color: #f1f3f4;
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: rgba(100, 68, 153, 0.05);
}

/* Avatar styling */
.avatar-sm.bg-primary {
    background-color: #644499 !important;
}

/* Text colors */
.text-muted {
    color: #6c757d !important;
}

/* Button hover effects */
.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>';

function timeAgo($datetime)
{
    $now = new DateTime();
    $messageTime = new DateTime($datetime);
    $diff = $now->diff($messageTime);

    if ($diff->days == 0 && $diff->y == 0 && $diff->m == 0) {
        return $messageTime->format('g:i a');
    }
    elseif ($diff->days < 7 && $diff->y == 0 && $diff->m == 0) {
        return $messageTime->format('D');
    }
    elseif ($diff->y == 0) {
        return $messageTime->format('M j');
    }
    else {
        return $messageTime->format('M j, Y');
    }
}

include '../layouts/app.php';
