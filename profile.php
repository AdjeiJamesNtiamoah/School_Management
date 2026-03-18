<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$u_id = $_SESSION['user_id'];
$status_msg = "";
$msg_type = "success";

// Handle Profile Updates (Name & Email)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $conn->query("UPDATE users SET full_name = '$full_name', email = '$email' WHERE id = $u_id");
    $status_msg = "Profile updated successfully!";
}

// Handle Photo Upload
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $new_file_name = $target_dir . "user_" . $u_id . "_" . time() . ".jpg";
    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $new_file_name)) {
        $conn->query("UPDATE users SET profile_pic = '$new_file_name' WHERE id = $u_id");
        $status_msg = "Photo updated!";
    }
}

$user = $conn->query("SELECT * FROM users WHERE id = $u_id")->fetch_assoc();
$user_photo = !empty($user['profile_pic']) ? $user['profile_pic'] : 'uploads/default.png';
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | Flawless</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #020617; --sidebar: #0f172a; --card: #1e293b; --text: #f8fafc; --muted: #94a3b8; --border: #334155; --accent: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); }
        [data-theme="light"] { --bg: #f1f5f9; --sidebar: #ffffff; --card: #ffffff; --text: #0f172a; --muted: #64748b; --border: #e2e8f0; }

        body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; display: flex; transition: 0.3s; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--sidebar); height: 100vh; padding: 30px 20px; border-right: 1px solid var(--border); position: fixed; display: flex; flex-direction: column; }
        .sidebar-brand { font-size: 1.5rem; font-weight: 800; background: var(--accent); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 40px; }
        
        .nav-links a { display: block; padding: 14px 18px; color: var(--muted); text-decoration: none; font-weight: 600; border-radius: 12px; margin-bottom: 8px; transition: 0.2s; }
        .nav-links a:hover, .nav-links a.active { background: var(--accent); color: white; box-shadow: 0 10px 15px -3px rgba(168, 85, 247, 0.2); }

        /* Main Content Area */
        .main { margin-left: 260px; width: 100%; padding: 40px 60px; box-sizing: border-box; }
        
        /* Profile Layout */
        .settings-container { display: grid; grid-template-columns: 320px 1fr; gap: 40px; align-items: start; }
        .glass-card { background: var(--card); padding: 30px; border-radius: 24px; border: 1px solid var(--border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        
        .profile-header { text-align: center; margin-bottom: 20px; }
        .profile-img-container { position: relative; width: 140px; height: 140px; margin: 0 auto 20px; }
        .profile-img { width: 100%; height: 100%; border-radius: 30%; object-fit: cover; border: 4px solid var(--bg); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        
        .upload-label { position: absolute; bottom: 5px; right: 5px; background: var(--accent); color: white; width: 35px; height: 35px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid var(--card); transition: 0.2s; }
        .upload-label:hover { transform: scale(1.1); }

        /* Forms */
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; color: var(--muted); font-size: 0.85rem; font-weight: 700; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        input { width: 100%; padding: 14px; background: var(--bg); border: 1px solid var(--border); border-radius: 12px; color: var(--text); box-sizing: border-box; font-family: inherit; transition: 0.2s; }
        input:focus { outline: none; border-color: #a855f7; box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.1); }
        
        .btn { background: var(--accent); color: white; border: none; padding: 14px 25px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; font-size: 0.95rem; }
        .btn:hover { opacity: 0.9; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }

        /* Notifications */
        .status-alert { position: fixed; top: 30px; right: 30px; z-index: 9999; padding: 16px 28px; border-radius: 16px; background: #10b981; color: white; font-weight: 700; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); animation: slideIn 0.5s ease; }
        @keyframes slideIn { from { transform: translateX(100px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">FLAWLESS</div>
    <nav class="nav-links">
        <a href="dashboard.php">Overview</a>
        <a href="profile.php" class="active">Account Settings</a>
    </nav>
    <div style="margin-top: auto;">
        <a href="logout.php" style="color: #f43f5e; font-weight: 700; text-decoration: none; padding: 10px;">Logout</a>
    </div>
</aside>

<main class="main">
    <header style="margin-bottom: 40px;">
        <h1 style="margin: 0; font-size: 2rem;">Settings</h1>
        <p style="color: var(--muted); margin-top: 5px;">Manage your profile and security preferences.</p>
    </header>

    <?php if($status_msg): ?>
        <div class="status-alert" id="autoAlert">
            <?php echo $status_msg; ?>
        </div>
    <?php endif; ?>

    <div class="settings-container">
        <div class="glass-card">
            <div class="profile-header">
                <div class="profile-img-container">
                    <img src="<?php echo $user_photo; ?>" class="profile-img">
                    <form id="photoForm" method="POST" enctype="multipart/form-data">
                        <label for="pic" class="upload-label">📷</label>
                        <input type="file" id="pic" name="profile_pic" style="display:none;" onchange="document.getElementById('photoForm').submit()">
                    </form>
                </div>
                <h3 style="margin-bottom: 5px;"><?php echo $user['full_name']; ?></h3>
                <span style="background: rgba(168, 85, 247, 0.1); color: #a855f7; padding: 4px 12px; border-radius: 8px; font-weight: 700; font-size: 0.75rem;">
                    <?php echo strtoupper($user['role']); ?>
                </span>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 30px;">
            
            <div class="glass-card">
                <h3 style="margin-top: 0; margin-bottom: 25px;">Personal Information</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn">Update Information</button>
                </form>
            </div>

            <div class="glass-card">
                <h3 style="margin-top: 0;">Security & Password</h3>
                <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 25px;">Update your password to keep your account safe.</p>
                <form method="POST" action="change_password.php">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_pass" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_pass" placeholder="At least 8 characters" required>
                    </div>
                    <button type="submit" class="btn" style="background: transparent; border: 2px solid #a855f7; color: #a855f7;">Change Password</button>
                </form>
            </div>

        </div>
    </div>
</main>

<script>
    // Universal Theme Loader
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);

    // Auto-Hide Notification
    const alertBox = document.getElementById('autoAlert');
    if(alertBox) {
        setTimeout(() => {
            alertBox.style.opacity = '0';
            alertBox.style.transform = 'translateX(20px)';
            setTimeout(() => alertBox.remove(), 500);
        }, 4000);
    }
</script>
</body>
</html>