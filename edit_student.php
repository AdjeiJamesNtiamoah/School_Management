<?php
include 'db.php';
session_start();

$id = $_GET['id'];
$res = $conn->query("SELECT * FROM users WHERE id = $id");
$s = $res->fetch_assoc();

if (isset($_POST['update'])) {
    $name = $_POST['full_name'];
    $class = $_POST['student_class'];
    $email = $_POST['email'];
    
    $conn->query("UPDATE users SET full_name='$name', student_class='$class', email='$email' WHERE id=$id");
    header("Location: dashboard.php?msg=updated");
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Edit Student | Flawless</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #020617; --card: #1e293b; --text: #f8fafc; --muted: #94a3b8; --border: #334155; --accent: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); }
        body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .edit-box { background: var(--card); padding: 40px; border-radius: 24px; border: 1px solid var(--border); width: 100%; max-width: 400px; }
        input, select { width: 100%; padding: 12px; margin: 10px 0 20px; background: var(--bg); border: 1px solid var(--border); border-radius: 12px; color: var(--text); }
        .btn { background: var(--accent); color: white; border: none; padding: 12px; border-radius: 12px; width: 100%; font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>

<div class="edit-box">
    <h2 style="margin-top:0;">Edit Student</h2>
    <form method="POST">
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?php echo $s['full_name']; ?>">
        
        <label>Email</label>
        <input type="email" name="email" value="<?php echo $s['email']; ?>">

        <label>Class</label>
        <select name="student_class">
            <option value="JHS 1" <?php if($s['student_class'] == 'JHS 1') echo 'selected'; ?>>JHS 1</option>
            <option value="JHS 2" <?php if($s['student_class'] == 'JHS 2') echo 'selected'; ?>>JHS 2</option>
            <option value="JHS 3" <?php if($s['student_class'] == 'JHS 3') echo 'selected'; ?>>JHS 3</option>
            <option value="SHS 1" <?php if($s['student_class'] == 'SHS 1') echo 'selected'; ?>>SHS 1</option>
            <option value="SHS 2" <?php if($s['student_class'] == 'SHS 2') echo 'selected'; ?>>SHS 2</option>
            <option value="SHS 3" <?php if($s['student_class'] == 'SHS 3') echo 'selected'; ?>>SHS 3</option>
        </select>

        <button type="submit" name="update" class="btn">Save Changes</button>
        <a href="dashboard.php" style="display:block; text-align:center; margin-top:15px; color:var(--muted); text-decoration:none; font-size:0.8rem;">Cancel</a>
    </form>
</div>

</body>
</html>