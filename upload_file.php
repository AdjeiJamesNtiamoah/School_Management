<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) exit();

$u_id = $_SESSION['user_id'];
$receiver_id = $_POST['admin_id'];
$upload_dir = 'uploads/';

if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

if (isset($_FILES['chat_file'])) {
    $file = $_FILES['chat_file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target = $upload_dir . $filename;

    // Allowed types
    $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'zip'];

    if (in_array(strtolower($ext), $allowed)) {
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $conn->query("INSERT INTO messages (sender_id, receiver_id, file_path, status) 
                         VALUES ($u_id, $receiver_id, '$target', 'unread')");
            echo "success";
        }
    }
}
?>