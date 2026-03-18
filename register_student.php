<?php
include 'db.php';
session_start();

// Only allow teachers to access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$message = "";
$msg_type = "";

if (isset($_POST['register_student'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $student_class = $_POST['student_class'];
    $default_password = password_hash("123456", PASSWORD_DEFAULT); // Default password
    $role = 'student';

    // Check if email already exists
    $check_email = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows > 0) {
        $message = "Error: A student with this email already exists!";
        $msg_type = "error";
    } else {
        // Insert new student
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, student_class) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $email, $default_password, $role, $student_class);
        
        if ($stmt->execute()) {
            $message = "Student registered successfully! Default password is '123456'";
            $msg_type = "success";
        } else {
            $message = "Error registering student.";
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Student | School Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 450px; border: 1px solid #e2e8f0; }
        h2 { margin-bottom: 10px; color: #1e293b; }
        p { color: #64748b; font-size: 14px; margin-bottom: 30px; }
        input, select { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #e2e8f0; border-radius: 10px; box-sizing: border-box; font-family: inherit; }
        .btn { width: 100%; padding: 14px; background: var(--primary); color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn:hover { background: #1d4ed8; }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .success { background: #ecfdf5; color: #059669; }
        .error { background: #fef2f2; color: #dc2626; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #64748b; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Register New Student</h2>
    <p>Add a student to your class records.</p>

    <?php if ($message): ?>
        <div class="alert <?php echo $msg_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="full_name" placeholder="Student Full Name" required>
        <input type="email" name="email" placeholder="Student Email Address" required>
        
        <select name="student_class" required>
            <option value="">Select Class</option>
            <option value="JHS 1">JHS 1</option>
            <option value="JHS 2">JHS 2</option>
            <option value="JHS 3">JHS 3</option>
            <option value="Primary 6">Primary 6</option>
        </select>

        <button type="submit" name="register_student" class="btn">Register Student</button>
    </form>

    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
</div>

</body>
</html>