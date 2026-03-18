<?php
include 'db.php';
session_start();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$my_id = $_SESSION['user_id'];

if ($action == 'initiate') {
    $receiver = (int)$_POST['receiver_id'];
    $type = $_POST['type'];
    $offer = $_POST['offer']; // The WebRTC SDP offer
    
    $stmt = $conn->prepare("INSERT INTO calls (caller_id, receiver_id, type, rtc_offer) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $my_id, $receiver, $type, $offer);
    $stmt->execute();
    echo json_encode(['success' => true, 'call_id' => $stmt->insert_id]);

} elseif ($action == 'check_incoming') {
    // Look for any pending calls where I am the receiver
    $result = $conn->query("SELECT c.*, u.full_name FROM calls c JOIN users u ON c.caller_id = u.id 
                            WHERE c.receiver_id = $my_id AND c.status = 'pending' LIMIT 1");
    echo json_encode($result->fetch_assoc());

} elseif ($action == 'respond') {
    $call_id = (int)$_POST['call_id'];
    $status = $_POST['status']; // 'accepted' or 'declined'
    $answer = $_POST['answer'] ?? null;
    
    $stmt = $conn->prepare("UPDATE calls SET status = ?, rtc_answer = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $answer, $call_id);
    $stmt->execute();
    echo json_encode(['success' => true]);
}
?>