<?php
include 'db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role  = mysqli_real_escape_string($conn, $_POST['role']);
    // Secure encryption
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $message = "<div class='error'>Email already in use.</div>";
    } else {
        $sql = "INSERT INTO users (full_name, email, password, role) VALUES ('$name', '$email', '$pass', '$role')";
        if ($conn->query($sql)) {
            $message = "<div class='success'>Registration successful! <a href='login.php'>Login here</a></div>";
        } else {
            $message = "<div class='error'>System error. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Flawless Management</title>
    <style>
        :root { --accent: #8b5cf6; --bg: #0c0d11; --card: #1c1e26; --border: #2d2f39; }
        body { background: var(--bg); color: white; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .register-card { background: var(--card); padding: 40px; border-radius: 24px; border: 1px solid var(--border); width: 100%; max-width: 400px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        input, select { width: 100%; padding: 14px; margin-bottom: 15px; background: #020617; border: 1px solid var(--border); border-radius: 12px; color: white; box-sizing: border-box; }
        .role-toggle { display: flex; gap: 10px; margin-bottom: 20px; background: #020617; padding: 5px; border-radius: 12px; }
        .role-btn { flex: 1; padding: 10px; border: none; background: transparent; color: #94a3b8; cursor: pointer; border-radius: 8px; font-weight: 600; }
        .role-btn.active { background: var(--accent); color: white; }
        .btn { width: 100%; padding: 14px; background: var(--accent); border: none; border-radius: 12px; color: white; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .success { color: #10b981; text-align: center; margin-bottom: 15px; }
        .error { color: #f43f5e; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="register-card">
        <h2 style="text-align: center;">Create Account</h2>
        <?= $message ?>
        <form action="register.php" method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            
            <label style="font-size: 0.8rem; color: #94a3b8; margin-left: 5px;">Select Your Role:</label>
            <div class="role-toggle">
                <button type="button" class="role-btn active" onclick="setRole('student', this)">Student</button>
                <button type="button" class="role-btn" onclick="setRole('teacher', this)">Teacher</button>
                <button type="button" class="role-btn" onclick="setRole('finance', this)">Finance</button>
                <button type="button" class="role-btn" onclick="setRole('admin', this)">Admin</button>
            </div>
            <input type="hidden" name="role" id="selectedRole" value="student">

            <button type="submit" class="btn">Register Now</button>
        </form>
    </div>

    <script>
        function setRole(role, btn) {
            document.getElementById('selectedRole').value = role;
            document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
    </script>
</body>
</html>