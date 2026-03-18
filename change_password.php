<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) { exit("Unauthorized"); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u_id = $_SESSION['user_id'];
    $current_pass = $_POST['current_pass'];
    $new_pass = $_POST['new_pass'];

    // 1. Fetch the user's current hashed password from the DB
    $res = $conn->query("SELECT password FROM users WHERE id = $u_id");
    $user = $res->fetch_assoc();

    // 2. Verify the "Current Password" input against the DB hash
    if (password_verify($current_pass, $user['password'])) {
        
        // 3. Hash the NEW password
        $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        
        // 4. Update the Database
        $update = $conn->query("UPDATE users SET password = '$hashed_new_pass' WHERE id = $u_id");

        if ($update) {
            header("Location: profile.php?msg=pass_success");
        } else {
            header("Location: profile.php?msg=error");
        }
    } else {
        // Current password didn't match
        header("Location: profile.php?msg=wrong_pass");
    }
}
exit();
?>