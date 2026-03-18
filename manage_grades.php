<?php
include 'db.php';
session_start();

if ($_SESSION['role'] != 'teacher') {
    header("Location: dashboard.php");
    exit();
}

$message = "";

// Handle Grade Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $subject = $_POST['subject'];
    $ca = $_POST['ca_score'];
    $exam = $_POST['exam_score'];
    $term = $_POST['term'];
    $teacher_id = $_SESSION['user_id'];

    $sql = "INSERT INTO grades (student_id, subject_name, ca_score, exam_score, term, graded_by) 
            VALUES ('$student_id', '$subject', '$ca', '$exam', '$term', '$teacher_id')";
    
    if ($conn->query($sql)) {
        $message = "Grade recorded successfully!";
    }
}

$students = $conn->query("SELECT id, full_name FROM users WHERE role = 'student'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Grades | School Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; padding: 40px; }
        .grade-container { max-width: 800px; margin: auto; background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 8px; }
        select, input { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .btn-submit { grid-column: span 2; background: #059669; color: white; padding: 14px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px; }
        .btn-submit:hover { background: #047857; }
        .success-msg { background: #ecfdf5; color: #059669; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>

<div class="grade-container">
    <h2>Grade Entry</h2>
    <p style="color: #64748b;">Enter assessment and exam scores for students.</p>

    <?php if ($message): ?>
        <div class="success-msg"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-grid">
            <div style="grid-column: span 2;">
                <label>Select Student</label>
                <select name="student_id" required>
                    <?php while($s = $students->fetch_assoc()): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo $s['full_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label>Subject</label>
                <input type="text" name="subject" placeholder="e.g. Mathematics" required>
            </div>

            <div>
                <label>Term</label>
                <select name="term">
                    <option>First Term</option>
                    <option>Second Term</option>
                    <option>Third Term</option>
                </select>
            </div>

            <div>
                <label>Class Assessment (Max 30)</label>
                <input type="number" name="ca_score" max="30" required>
            </div>

            <div>
                <label>Exam Score (Max 70)</label>
                <input type="number" name="exam_score" max="70" required>
            </div>

            <button type="submit" class="btn-submit">Save Grades</button>
        </div>
    </form>
    <br>
    <a href="dashboard.php" style="color: #64748b; text-decoration: none; font-size: 14px;">← Back to Dashboard</a>
</div>

</body>
</html>