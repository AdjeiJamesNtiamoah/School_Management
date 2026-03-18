<?php
include 'db.php';
session_start();

$u_id = $_SESSION['user_id'];
$receiver_id = (int)$_GET['receiver_id'];

// Check if the OTHER person is typing TO YOU
// We check if last_updated was in the last 5 seconds to prevent "stuck" indicators
$query = "SELECT is_typing FROM typing_status 
          WHERE user_id = $receiver_id AND receiver_id = $u_id 
          AND last_updated > NOW() - INTERVAL 5 SECOND";

$result = $conn->query($query);
$data = $result->fetch_assoc();

echo json_encode(['is_typing' => ($data && $data['is_typing'] == 1)]);
?>