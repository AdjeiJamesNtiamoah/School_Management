<?php
include 'db.php';
session_start();
$u_id = $_SESSION['user_id'];

$name = $_POST['name'];
$email = $_POST['email'];
$new_pass = $_POST['new_pass'];

// 1. Update Name and Email
$conn->query("UPDATE users SET full_name = '$name', email = '$email' WHERE id = $u_id");

// 2. Handle Password if provided
if(!empty($new_pass)) {
    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password = '$hashed' WHERE id = $u_id");
}

// 3. Handle File Upload
if(isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "avatar_" . $u_id . "_" . time() . "." . $ext;
    $target = "uploads/" . $filename;

    if(move_uploaded_file($file['tmp_name'], $target)) {
        $conn->query("UPDATE users SET profile_pic = '$target' WHERE id = $u_id");
    }
}

echo json_encode(['success' => true]);
?>