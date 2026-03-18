<?php
// Enable error reporting to see what's wrong
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) { 
    die("Error: User session not found. Please log in again."); 
}

$u_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');

if ($action == 'clock_in') {
    // Check if already clocked in to prevent duplicates
    $check = $conn->query("SELECT * FROM attendance_logs WHERE user_id = $u_id AND clock_out IS NULL");
    if ($check->num_rows == 0) {
        $sql = "INSERT INTO attendance_logs (user_id, clock_in, log_date) VALUES ($u_id, '$now', '$today')";
        if ($conn->query($sql)) {
            header("Location: dashboard.php?msg=clocked_in");
        } else {
            die("DB Error: " . $conn->error);
        }
    } else {
        header("Location: dashboard.php?msg=already_in");
    }
} 
elseif ($action == 'clock_out') {
    $sql = "UPDATE attendance_logs SET clock_out = '$now' WHERE user_id = $u_id AND clock_out IS NULL ORDER BY id DESC LIMIT 1";
    if ($conn->query($sql)) {
        header("Location: dashboard.php?msg=clocked_out");
    } else {
        die("DB Error: " . $conn->error);
    }
} else {
    die("Error: No action specified. Did you click the button?");
}
exit();
?>