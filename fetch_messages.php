<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) { exit(); }

$u_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;

if ($receiver_id === 0) exit();

// 1. Mark as read
$conn->query("UPDATE messages SET status = 'read' WHERE sender_id = $receiver_id AND receiver_id = $u_id AND status = 'unread'");

// 2. Fetch conversation
$query = "SELECT * FROM messages 
          WHERE (sender_id = $u_id AND receiver_id = $receiver_id) 
          OR (sender_id = $receiver_id AND receiver_id = $u_id) 
          ORDER BY created_at ASC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $is_mine = ($row['sender_id'] == $u_id); 
        $msg_text = htmlspecialchars($row['message_text']);
        $time = date('H:i', strtotime($row['created_at']));
        
        // Setup Visuals
        $bg = $is_mine ? '#005c4b' : '#202c33'; 
        $align = $is_mine ? 'flex-end' : 'flex-start';
        $radius = $is_mine ? '15px 15px 2px 15px' : '15px 15px 15px 2px';

        // FIX: Display Image if path exists in DB
        $img_html = "";
        if (!empty($row['message_image']) && file_exists($row['message_image'])) {
            $img_html = "<img src='{$row['message_image']}' style='max-width: 100%; border-radius: 8px; margin-bottom: 8px; cursor: pointer;' onclick='window.open(this.src)'>";
        }

        echo "
        <div style='display: flex; justify-content: $align; margin-bottom: 12px; width: 100%;'>
            <div style='background: $bg; color: #ffffff !important; padding: 10px 14px; border-radius: $radius; max-width: 75%; box-shadow: 0 1px 2px rgba(0,0,0,0.3);'>
                $img_html
                " . (!empty($msg_text) ? "<div style='font-size: 0.95rem; line-height: 1.4; word-wrap: break-word;'>$msg_text</div>" : "") . "
                <div style='font-size: 0.65rem; opacity: 0.8; margin-top: 4px; text-align: right;'>
                    $time " . ($is_mine ? '✓✓' : '') . "
                </div>
            </div>
        </div>";
    }
}
?>
