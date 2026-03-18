<?php
include 'db.php';
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $attempted_role = mysqli_real_escape_string($conn, $_POST['attempted_role']); // From hidden input

    // Only fetch the user if BOTH email and the selected role match
    $result = $conn->query("SELECT * FROM users WHERE email = '$email' AND role = '$attempted_role'");

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on verified role
            $redirects = [
                'admin' => 'admin_dashboard.php',
                'teacher' => 'dashboard.php',
                'student' => 'student_dashboard.php',
                'finance' => 'finance_dashboard.php'
            ];
            header("Location: " . $redirects[$user['role']]);
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No $attempted_role account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Login | Flawless</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #020617; --card: #0f172a; --text: #f8fafc; --accent: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); --border: #334155; }
        body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: var(--card); padding: 40px; border-radius: 24px; border: 1px solid var(--border); width: 100%; max-width: 400px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
        .brand { font-size: 2rem; font-weight: 800; background: var(--accent); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-align: center; margin-bottom: 30px; }
        input { width: 100%; padding: 14px; margin-bottom: 20px; background: #020617; border: 1px solid var(--border); border-radius: 12px; color: white; box-sizing: border-box; }
        .btn { width: 100%; padding: 14px; background: var(--accent); border: none; border-radius: 12px; color: white; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn:hover { transform: translateY(-2px); opacity: 0.9; }
        .error { color: #f43f5e; text-align: center; margin-bottom: 20px; font-weight: 600; }

        .role-selection {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    background: #020617;
    padding: 5px;
    border-radius: 12px;
}
.role-tab {
    flex: 1;
    padding: 10px;
    border: none;
    background: transparent;
    color: #94a3b8;
    cursor: pointer;
    font-weight: 600;
    transition: 0.3s;
    border-radius: 8px;
}
.role-tab.active {
    background: var(--accent); /* Your purple/blue accent */
    color: white;
}
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand">FLAWLESS</div>
    
    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
<div class="role-selection">
    <button type="button" class="role-tab active" onclick="setRole('student', this)">Student</button>
    <button type="button" class="role-tab" onclick="setRole('teacher', this)">Teacher</button>
    <button type="button" class="role-tab" onclick="setRole('admin', this)">Admin</button>
    <button type="button" class="role-tab" onclick="setRole('finance', this)">Finance</button>
</div>

<form id="loginForm" action="login.php" method="POST">
    <input type="hidden" name="attempted_role" id="attempted_role" value="student">
    
    <input type="email" name="email" placeholder="Email Address" required>
    <input type="password" name="password" placeholder="Password" required>
    
    <button type="submit" class="btn">Login</button>
    <div style="margin-top: 25px; text-align: center; border-top: 1px solid var(--border); padding-top: 20px;">
    <p style="color: #94a3b8; font-size: 0.9rem;">
        Don't have an account? 
        <a href="register.php" style="color: var(--accent); font-weight: 600; text-decoration: none;">Create one now</a>
    </p>
</div>
</form>
</div>

<script>
function setRole(role, element) {
    document.getElementById('attempted_role').value = role;
    document.querySelectorAll('.role-tab').forEach(tab => tab.classList.remove('active'));
    element.classList.add('active');
}
</script>
</body>
</html>