<?php
include 'db.php';
session_start();

if (isset($_SESSION['user_id']) && isset($_POST['sender_id'])) {
    $u_id = $_SESSION['user_id'];
    $sender_id = (int)$_POST['sender_id'];

    // Update messages where the current user is the receiver
    $query = "UPDATE messages SET status = 'read' 
              WHERE receiver_id = ? AND sender_id = ? AND status = 'unread'";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $u_id, $sender_id);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
}
?>