<?php
include 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$message = trim($_POST['message'] ?? '');
$attachment_path = null;

// Handle File Upload Logic
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/chat/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('msg_', true) . '.' . $file_ext;
    $target_file = $upload_dir . $new_filename;

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
        $attachment_path = $target_file;
        // If message is empty, set a placeholder so the UI knows a file was sent
        if (empty($message)) {
            $message = "Sent a file";
        }
    }
}

// Generate Conversation ID
$ids = [$sender_id, $receiver_id];
sort($ids);
$conversation_id = $ids[0] . "_" . $ids[1];

$query = "INSERT INTO messages (sender_id, receiver_id, conversation_id, message, attachment, status) VALUES (?, ?, ?, ?, ?, 'unread')";
$stmt = $conn->prepare($query);
$stmt->bind_param("iisss", $sender_id, $receiver_id, $conversation_id, $message, $attachment_path);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>