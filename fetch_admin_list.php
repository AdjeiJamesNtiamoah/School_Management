<?php
include 'db.php';
session_start();
$u_id = $_SESSION['user_id'];

// Fetch all users for Admin
$res = $conn->query("SELECT id, full_name, role FROM users WHERE id != $u_id ORDER BY full_name ASC");

while($row = $res->fetch_assoc()) {
    $cid = $row['id'];
    $name = htmlspecialchars($row['full_name']);
    $role = ucfirst($row['role']);
    
    echo "
    <div class='admin-contact-card' onclick=\"openAdminChat($cid, '$name')\">
        <div class='contact-avatar'>" . strtoupper(substr($name, 0, 1)) . "</div>
        <div class='contact-details'>
            <div class='contact-name'>$name</div>
            <div class='contact-role'>$role</div>
        </div>
    </div>";
}
?>