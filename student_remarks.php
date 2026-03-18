<?php
include 'db.php';
session_start();

if ($_SESSION['role'] != 'teacher') { header("Location: dashboard.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sid = $_POST['student_id'];
    $tid = $_SESSION['user_id'];
    $cat = $_POST['category'];
    $rem = $_POST['remark'];

    $sql = "INSERT INTO student_remarks (student_id, teacher_id, category, remark) VALUES ('$sid', '$tid', '$cat', '$rem')";
    $conn->query($sql);
    $success = "Remark saved successfully!";
}

$students = $conn->query("SELECT id, full_name FROM users WHERE role = 'student'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Remarks</title>
    <style>
        body { font-family: sans-serif; background: #f8fafc; padding: 2rem; }
        .form-box { max-width: 500px; margin: auto; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        select, textarea, button { width: 100%; margin-top: 10px; padding: 10px; border-radius: 6px; border: 1px solid #ddd; }
        button { background: #db2777; color: white; border: none; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Add Student Remark</h2>
        <form method="POST">
            <label>Select Student</label>
            <select name="student_id">
                <?php while($s = $students->fetch_assoc()) echo "<option value='".$s['id']."'>".$s['full_name']."</option>"; ?>
            </select>
            
            <label>Category</label>
            <select name="category">
                <option>Behavioral</option>
                <option>Academic</option>
                <option>General</option>
            </select>

            <label>Remark Details</label>
            <textarea name="remark" rows="4" placeholder="Describe student progress or issues..."></textarea>
            
            <button type="submit">Save Remark</button>
        </form>
    </div>
</body>
</html>