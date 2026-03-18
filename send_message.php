<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = (int)$_POST['receiver_id'];
    
    // FIX: Corrected the function name
    $message_text = mysqli_real_escape_string($conn, $_POST['message_body']);
    $image_path = null;

    // Handle Image Upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $target_dir = "uploads/chat/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES["attachment"]["name"], PATHINFO_EXTENSION);
        $new_name = "IMG_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;
        $target_file = $target_dir . $new_name;

        if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    if (!empty($message_text) || !empty($image_path)) {
        $sql = "INSERT INTO messages (sender_id, receiver_id, message_text, message_image, status) 
                VALUES ($sender_id, $receiver_id, '$message_text', '$image_path', 'unread')";
        $conn->query($sql);
    }



}
?>
