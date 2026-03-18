<?php
include 'db.php';
session_start();
$u_id = $_SESSION['user_id'];

// Get all users except the current admin
$query = "SELECT id, full_name, role FROM users WHERE id != $u_id ORDER BY full_name ASC";
$result = $conn->query($query);

while($row = $result->fetch_assoc()) {
    $cid = $row['id'];
    $name = htmlspecialchars($row['full_name']);
    $role = ucfirst($row['role']);
    
    // Check for unread messages
    $unread_q = $conn->query("SELECT COUNT(*) as total FROM messages WHERE sender_id = $cid AND receiver_id = $u_id AND status = 'unread'");
    $unread = $unread_q->fetch_assoc()['total'];

    echo "
    <div class='contact-item' onclick=\"openAdminChat($cid, '$name')\">
        <div class='contact-info'>
            <div class='contact-name'>$name</div>
            <div class='contact-role'>$role</div>
        </div>";
    
    if($unread > 0) {
        echo "<span class='unread-pill'>$unread</span>";
    }
    
    echo "</div>";
}
?>