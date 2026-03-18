<?php
include 'db.php';
session_start();

if ($_SESSION['role'] !== 'admin') exit("Unauthorized");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['msg']);
    $admin_id = $_SESSION['user_id'];

    $sql = "INSERT INTO announcements (title, message, posted_by) VALUES ('$title', '$message', $admin_id)";
    
    if ($conn->query($sql)) {
        header("Location: admin_dashboard.php?broadcast=success");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>