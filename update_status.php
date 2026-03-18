<?php
// update_status.php
include 'db.php';
session_start();
if(isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $conn->query("UPDATE users SET last_seen = NOW() WHERE id = $uid");
}
?>