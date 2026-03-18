<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$u_id = $_SESSION['user_id'];
$query = $conn->query("SELECT COUNT(*) as c FROM messages WHERE status = 'unread' AND receiver_id = $u_id");
$row = $query->fetch_assoc();

echo json_encode(['count' => (int)$row['c']]);
?>