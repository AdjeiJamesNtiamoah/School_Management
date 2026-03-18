<?php
include 'db.php';
session_start();

if ($_SESSION['role'] != 'teacher') { header("Location: dashboard.php"); exit(); }

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tid = $_SESSION['user_id'];
    $subject = $_POST['subject_name'];
    $topic = $_POST['topic'];
    $date = $_POST['lesson_date'];
    $content = $_POST['content'];
    $file_dest = NULL;

    // Handle File Upload if exists
    if (!empty($_FILES['lesson_file']['name'])) {
        $target_dir = "uploads/";
        $file_name = "LP_" . time() . "_" . basename($_FILES["lesson_file"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["lesson_file"]["tmp_name"], $target_file)) {
            $file_dest = $target_file;
        }
    }

    $stmt = $conn->prepare("INSERT INTO lesson_plans (teacher_id, subject_name, topic, lesson_date, content, file_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $tid, $subject, $topic, $date, $content, $file_dest);
    
    if ($stmt->execute()) {
        $message = "Lesson plan and materials saved successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Professional Lesson Planner</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; padding: 40px; }
        .planner-card { max-width: 900px; margin: auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        label { display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px; }
        input, select, textarea { width: 100%; padding: 12px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-family: inherit; }
        textarea { height: 250px; resize: vertical; }
        .file-input-wrapper { background: #f1f5f9; padding: 20px; border-radius: 10px; border: 2px dashed #cbd5e1; margin-top: 20px; text-align: center; }
        .btn-save { background: #ea580c; color: white; border: none; padding: 15px 30px; border-radius: 10px; font-weight: 600; cursor: pointer; margin-top: 20px; width: 100%; }
        .btn-save:hover { background: #c2410c; }
        .success-banner { background: #ecfdf5; color: #059669; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #d1fae5; text-align: center;}
    </style>
</head>
<body>

<div class="planner-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2>📝 Professional Lesson Planner</h2>
        <a href="dashboard.php" style="text-decoration: none; color: #64748b; font-size: 14px;">🏠 Dashboard</a>
    </div>

    <?php if ($message) echo "<div class='success-banner'>$message</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div>
                <label>Subject</label>
                <input type="text" name="subject_name" placeholder="e.g. Physics" required>
            </div>
            <div>
                <label>Topic / Unit</label>
                <input type="text" name="topic" placeholder="e.g. Thermodynamics" required>
            </div>
            <div>
                <label>Teaching Date</label>
                <input type="date" name="lesson_date" required>
            </div>
        </div>

        <label>Lesson Content & Teaching Methodology</label>
        <textarea name="content" placeholder="Type your lesson notes, objectives, and evaluation methods here..."></textarea>
        
        <div class="file-input-wrapper">
            <label style="text-align: center;">📎 Attach Lesson Resources (PDF, Slides, Images)</label>
            <input type="file" name="lesson_file" style="border: none; background: transparent;">
        </div>
        
        <button type="submit" class="btn-save">Save & Archive Plan</button>
    </form>
</div>

</body>
</html>