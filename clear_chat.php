<?php
include 'db.php';
session_start();

if (isset($_SESSION['user_id']) && isset($_GET['receiver_id'])) {
    $u_id = $_SESSION['user_id'];
    $receiver_id = (int)$_GET['receiver_id'];

    // Delete messages where you are either the sender or receiver in this specific conversation
    $sql = "DELETE FROM messages 
            WHERE (sender_id = $u_id AND receiver_id = $receiver_id) 
               OR (sender_id = $receiver_id AND receiver_id = $u_id)";
    
    if ($conn->query($sql)) {
        header("Location: messages.php?msg=cleared");
    }
}
?>