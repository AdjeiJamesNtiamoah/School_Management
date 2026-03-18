<?php
include 'db.php';
session_start();

// Security: Only Admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// 1. Fetch existing user data
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT id, full_name, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        die("User not found.");
    }
}

// 2. Handle the update submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $uid = (int)$_POST['user_id'];
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $update_sql = "UPDATE users SET full_name = ?, email = ?, role = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssi", $name, $email, $role, $uid);

    if ($update_stmt->execute()) {
        $message = "<div class='alert success'>User updated successfully! <a href='admin_dashboard.php'>Return to Dashboard</a></div>";
        // Refresh local data for the form
        $user['full_name'] = $name;
        $user['email'] = $email;
        $user['role'] = $role;
    } else {
        $message = "<div class='alert error'>Update failed: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | Flawless Admin</title>
    <style>
        :root { --bg: #020617; --panel: #1e293b; --accent: #8b5cf6; --text: #f8fafc; --border: rgba(255,255,255,0.06); }
        body { background: var(--bg); color: var(--text); font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .edit-card { background: var(--panel); padding: 40px; border-radius: 24px; border: 1px solid var(--border); width: 100%; max-width: 450px; }
        input, select { width: 100%; padding: 12px; margin: 10px 0 20px; background: #0c0d11; border: 1px solid var(--border); border-radius: 12px; color: white; box-sizing: border-box; }
        .btn-save { width: 100%; padding: 14px; background: var(--accent); border: none; border-radius: 12px; color: white; font-weight: 700; cursor: pointer; }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .success { background: rgba(16, 185, 129, 0.2); color: #10b981; }
        .error { background: rgba(244, 63, 94, 0.2); color: #f43f5e; }
        a { color: var(--accent); text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="edit-card">
        <h2>Edit Member Details</h2>
        <?= $message ?>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            
            <label>Email Address</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            
            <label>System Role</label>
            <select name="role">
                <option value="finance" <?= $user['role'] == 'finance' ? 'selected' : '' ?>>Finance</option>
                <option value="student" <?= $user['role'] == 'student' ? 'selected' : '' ?>>Student</option>
                <option value="teacher" <?= $user['role'] == 'teacher' ? 'selected' : '' ?>>Teacher</option>
                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Administrator</option>
            </select>
            
            <button type="submit" name="update_user" class="btn-save">Save Changes</button>
            <p style="text-align:center; margin-top:20px;"><a href="admin_dashboard.php">Cancel and Go Back</a></p>
        </form>
    </div>
</body>
</html>