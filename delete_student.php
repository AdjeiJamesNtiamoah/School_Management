<?php
include 'db.php';
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Ensure we only delete students, not admins!
    $conn->query("DELETE FROM users WHERE id = $id AND role = 'student'");
    header("Location: dashboard.php?msg=deleted");
}
exit();
?>