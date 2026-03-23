<?php
session_start();
$header = "Chat Support";
if (empty($_SESSION['customer_id'])) {
    header('Location: ../auth/unified-login.php');
    exit();
}
require_once '../config/db_conn.php';
require_once 'req/chat-functions.php';

function formatMessageTime($datetime)
{
    $now = new DateTime();
    $messageTime = new DateTime($datetime);
    $diff = $now->diff($messageTime);

    if ($diff->days == 0 && $diff->y == 0 && $diff->m == 0) {
        return 'Today ' . $messageTime->format('g:i A');
    } elseif ($diff->days == 1 && $diff->y == 0 && $diff->m == 0) {
        return 'Yesterday ' . $messageTime->format('g:i A');
    } elseif ($diff->days < 7 && $diff->y == 0 && $diff->m == 0) {
        return $messageTime->format('D g:i A');
    } elseif ($diff->y == 0) {
        return $messageTime->format('M j g:i A');
    } else {
        return $messageTime->format('M j, Y g:i A');
    }
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];
$customer_email = $_SESSION['customer_email'];

$chat_room = getChatRoom($conn, $customer_id);
if (!$chat_room) {
    $room_id = createChatRoom($conn, $customer_id, $customer_name, $customer_email);
    $chat_room = getChatRoom($conn, $customer_id);
} else {
    $room_id = $chat_room['id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadChatImage($_FILES['image'], $room_id);
        if ($upload_result['success']) {
            $message = isset($_POST['message']) && !empty(trim($_POST['message'])) ? trim($_POST['message']) : 'Sent an image';
            sendMessage($conn, $room_id, $customer_id, 'customer', $customer_name, $message, 'image', $upload_result['path']);
        }
    } elseif (isset($_POST['message']) && !empty(trim($_POST['message']))) {
        $message = trim($_POST['message']);
        sendMessage($conn, $room_id, $customer_id, 'customer', $customer_name, $message);
    }
    header('Location: customer-chat.php');
    exit();
}

$messages = getChatMessages($conn, $room_id);

markMessagesAsRead($conn, $room_id, 'customer');

function formatMessageDate($datetime)
{
    $now = new DateTime();
    $messageTime = new DateTime($datetime);
    $diff = $now->diff($messageTime);

    if ($diff->days == 0 && $diff->y == 0 && $diff->m == 0) {
        return 'Today';
    } elseif ($diff->days == 1 && $diff->y == 0 && $diff->m == 0) {
        return 'Yesterday';
    } elseif ($diff->days < 7 && $diff->y == 0 && $diff->m == 0) {
        return $messageTime->format('l');
    } elseif ($diff->y == 0) {
        return $messageTime->format('F j');
    } else {
        return $messageTime->format('F j, Y');
    }
}

$slot = '
<link href="../assets/css/chat-styles.css" rel="stylesheet">
<link href="../assets/css/chat-mobile.css" rel="stylesheet">
<div class="chat-container">
    <div class="card chat-card">
        <div class="chat-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-comments me-2"></i>
                    Chat Support
                </h5>
                <small>Online Support</small>
            </div>
        </div>
        
        <div id="chatMessages" class="chat-messages">
            ' . (!empty($messages) ? '' : '
            <div class="text-center text-muted py-4">
                <i class="fas fa-comments fa-3x mb-3"></i>
                <p>Start a conversation with our support team!</p>
            </div>') . '
';

$currentDate = null;

foreach ($messages as $msg) {
    $messageDate = date('Y-m-d', strtotime($msg['created_at']));

    if ($messageDate != $currentDate) {
        $currentDate = $messageDate;
        $displayDate = formatMessageDate($msg['created_at']);

        $slot .= '
        <div class="date-separator text-center my-3">
            <span class="badge bg-light text-dark">' . $displayDate . '</span>
        </div>';
    }

    $isCustomer = $msg['sender_type'] === 'customer';
    $messageClass = $isCustomer ? 'customer-message' : 'support-message';
    $alignClass = $isCustomer ? 'text-end' : 'text-start';

    $seenTime = null;
    if ($isCustomer) {
        $seenTime = getMessageSeenTime($conn, $msg['id']);
    }

    $slot .= '
    <div class="message-item ' . $messageClass . ' ' . $alignClass . '">
        <div class="message-bubble d-inline-block">';
    
    if ($msg['message_type'] === 'image' && !empty($msg['image_path'])) {
        $slot .= '
            <div class="message-image">
                <img src="../' . htmlspecialchars($msg['image_path']) . '" alt="Image" class="chat-image" onclick="openImageModal(this.src)">
            </div>';
    }
    
    if (!empty($msg['message'])) {
        $slot .= '<div class="message-content">' . htmlspecialchars($msg['message']) . '</div>';
    }
    
    $slot .= '
            <div class="message-meta">
                ' . ($isCustomer ? 'You' : htmlspecialchars($msg['sender_name'])) . ' • 
                ' . date('g:i A', strtotime($msg['created_at'])) . '
            </div>
            <div class="message-status">
                ' . ($isCustomer ? '
                    <i class="fas fa-check' . ($seenTime ? '-double text-info' : '') . '"></i>
                    ' . ($seenTime ? ' Seen at ' . date('g:i A', strtotime($seenTime)) : ' Sent') . '
                ' : '') . '
            </div>
        </div>
    </div>';
}

$slot .= '
        </div>
        
        <div class="chat-input">
            <form method="POST" enctype="multipart/form-data" class="chat-form" id="chatForm">
                <input type="file" id="imageInput" name="image" accept="image/*" style="display: none;">
                <button type="button" class="btn btn-icon" onclick="document.getElementById(\'imageInput\').click()" title="Attach image">
                    <i class="fas fa-paperclip"></i>
                </button>
                <button type="button" class="btn btn-icon" onclick="openCamera()" title="Take photo">
                    <i class="fas fa-camera"></i>
                </button>
                <input type="text" name="message" id="messageInput" class="form-control" placeholder="Type your message..." autocomplete="off">
                <button type="submit" class="btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            <div id="imagePreview" style="display: none; padding: 10px;">
                <div class="d-flex align-items-center gap-2">
                    <img id="previewImg" src="" style="max-width: 100px; max-height: 100px; border-radius: 8px;">
                    <span id="previewName" class="text-muted small"></span>
                    <button type="button" class="btn btn-sm btn-danger ms-auto" onclick="clearImageSelection()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.9);">
    <span class="close" onclick="closeImageModal()" style="position: absolute; top: 15px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; cursor: pointer;">&times;</span>
    <img id="modalImage" style="margin: auto; display: block; max-width: 90%; max-height: 90%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
</div>

<style>
.main-content {
    position: relative !important;
    z-index: 1 !important;
}

.chat-messages {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
}

.message-item .bg-primary {
    background-color: #644499 !important;
}

.message-item .bg-light {
    background-color: #e9ecef;
    border: 1px solid #dee2e6;
    color: #333;
}

.message-item {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.card-header {
    border-bottom: 2px solid rgba(255,255,255,0.1);
}

.form-control:focus {
    border-color: #644499;
    box-shadow: 0 0 0 0.2rem rgba(100, 68, 153, 0.25);
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

.btn-outline-secondary {
    color: #644499;
    border-color: #644499;
}

.btn-outline-secondary:hover {
    background-color: #644499;
    border-color: #644499;
    color: white;
}

.container-fluid {
    max-width: 100% !important;
    padding-left: 15px !important;
    padding-right: 15px !important;
}

.chat-widget, .chat-support-widget {
    display: none !important;
}

.date-separator {
    position: relative;
    margin: 1.5rem 0;
}

.date-separator:before,
.date-separator:after {
    content: "";
    position: absolute;
    top: 50%;
    width: 45%;
    height: 1px;
    background-color: #dee2e6;
}

.date-separator:before {
    left: 0;
}

.date-separator:after {
    right: 0;
}

.date-separator .badge {
    position: relative;
    z-index: 1;
    padding: 0.35rem 0.75rem;
    font-weight: normal;
    font-size: 0.75rem;
    background-color: #f8f9fa !important;
    color: #6c757d !important;
    border: 1px solid #dee2e6;
}

.chat-form {
    display: flex;
    gap: 8px;
    align-items: center;
}

.btn-icon {
    background: none;
    border: none;
    color: #644499;
    padding: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-icon:hover {
    color: #644499;
    background-color: rgba(100, 68, 153, 0.1);
}

.chat-image {
    max-width: 300px;
    max-height: 300px;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s;
}

.chat-image:hover {
    transform: none;
}

.message-image {
    margin-bottom: 8px;
}

#imagePreview {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

/* Message bubble styles */
.message-bubble {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 18px;
    margin-bottom: 8px;
    position: relative;
}

.support-message .message-bubble {
    background-color: #644499;
    color: white;
    border-bottom-left-radius: 4px;
}

.customer-message .message-bubble {
    background-color: #e9ecef;
    color: #333;
    border-bottom-right-radius: 4px;
}

.message-meta {
    font-size: 0.75rem;
    opacity: 0.8;
    margin-top: 4px;
}

.message-status {
    font-size: 0.7rem;
    margin-top: 2px;
    color: #6c757d !important;
}

.chat-header {
    background: #644499;
    color: white;
    padding: 1rem 1.5rem;
}

.chat-card {
    border: none;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.chat-input {
    background-color: white;
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
}

.chat-messages {
    height: 500px;
    overflow-y: auto;
    padding: 1rem 1.5rem;
}

/* Modal styles */
#imageModal {
    z-index: 9999;
}

#imageModal .close {
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}

#imageModal .close:hover {
    color: #ccc;
}
</style>

<script>
document.getElementById("imageModal").addEventListener("click", function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});
document.addEventListener("DOMContentLoaded", function() {
    const chatMessages = document.getElementById("chatMessages");
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Image input preview
    document.getElementById("imageInput").addEventListener("change", function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById("previewImg").src = e.target.result;
                document.getElementById("previewName").textContent = file.name;
                document.getElementById("imagePreview").style.display = "block";
            };
            
            reader.readAsDataURL(file);
        }
    });
    
    // Auto-refresh messages
    setInterval(function() {
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");
                const newMessages = doc.getElementById("chatMessages");
                if (newMessages) {
                    chatMessages.innerHTML = newMessages.innerHTML;
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            })
            .catch(console.error);
    }, 5000);
    
    const form = document.querySelector("form");
    form.addEventListener("submit", function() {
        setTimeout(() => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 100);
    });
});

function clearImageSelection() {
    document.getElementById("imageInput").value = "";
    document.getElementById("imagePreview").style.display = "none";
}

async function openCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        
        const modal = document.createElement("div");
        modal.style.cssText = "position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.9); display: flex; flex-direction: column; align-items: center; justify-content: center;";
        
        const video = document.createElement("video");
        video.style.cssText = "max-width: 90%; max-height: 70%; border-radius: 8px;";
        video.autoplay = true;
        video.srcObject = stream;
        
        const controls = document.createElement("div");
        controls.style.cssText = "display: flex; gap: 20px; margin-top: 20px;";
        
        const captureBtn = document.createElement("button");
        captureBtn.innerHTML = \'<i class=\"fas fa-camera\"></i> Capture\';
        captureBtn.className = "btn btn-primary";
        captureBtn.onclick = () => {
            const canvas = document.createElement("canvas");
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext("2d").drawImage(video, 0, 0);
            
            canvas.toBlob((blob) => {
                const file = new File([blob], "camera_" + Date.now() + ".jpg", { type: "image/jpeg" });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById("imageInput").files = dataTransfer.files;
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById("previewImg").src = e.target.result;
                    document.getElementById("previewName").textContent = file.name;
                    document.getElementById("imagePreview").style.display = "block";
                };
                reader.readAsDataURL(file);
                
                stream.getTracks().forEach(track => track.stop());
                document.body.removeChild(modal);
            }, "image/jpeg");
        };
        
        const closeBtn = document.createElement("button");
        closeBtn.innerHTML = \'<i class=\"fas fa-times\"></i> Close\';
        closeBtn.className = "btn btn-secondary";
        closeBtn.onclick = () => {
            stream.getTracks().forEach(track => track.stop());
            document.body.removeChild(modal);
        };
        
        controls.appendChild(captureBtn);
        controls.appendChild(closeBtn);
        modal.appendChild(video);
        modal.appendChild(controls);
        document.body.appendChild(modal);
        
    } catch (error) {
        alert("Camera access denied or not available: " + error.message);
    }
}

function openImageModal(src) {
    document.getElementById("modalImage").src = src;
    document.getElementById("imageModal").style.display = "block";
}

function closeImageModal() {
    document.getElementById("imageModal").style.display = "none";
}

document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        closeImageModal();
    }
});
</script>';

include '../layouts/app-customer.php';