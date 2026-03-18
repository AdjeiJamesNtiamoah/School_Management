<?php
include 'db.php';
session_start();

if (isset($_SESSION['user_id']) && isset($_GET['receiver_id'])) {
    $u_id = $_SESSION['user_id'];
    $receiver_id = (int)$_GET['receiver_id'];
    $is_typing = (int)$_GET['status']; // 1 for typing, 0 for stopped

    // We use an "ON DUPLICATE KEY UPDATE" so we don't spam the database with new rows
    $sql = "INSERT INTO typing_status (user_id, receiver_id, is_typing) 
            VALUES ($u_id, $receiver_id, $is_typing)
            ON DUPLICATE KEY UPDATE is_typing = $is_typing, last_updated = NOW()";
    
    $conn->query($sql);
}
?>