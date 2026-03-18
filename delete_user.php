<?php
// Prevent any accidental output (like warnings) from breaking the JSON
ob_start(); 
include 'db.php';
session_start();

// Set header to JSON immediately
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error'];

try {
    // 1. Check Authorization
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Unauthorized access');
    }

    // 2. Validate Input
    if (!isset($_POST['id'])) {
        throw new Exception('Missing User ID');
    }

    $id = (int)$_POST['id'];

    // 3. Prevent Self-Deletion
    if ($id == $_SESSION['user_id']) {
        throw new Exception('Security Error: You cannot delete yourself.');
    }

    // 4. Execute Deletion
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $response = ['success' => true];
    } else {
        throw new Exception('Database error: ' . $conn->error);
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Clear any accidental whitespace/warnings and send clean JSON
ob_end_clean();
echo json_encode($response);
exit;