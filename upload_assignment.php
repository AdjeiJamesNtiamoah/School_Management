<?php
include 'db.php';
session_start();

if ($_SESSION['role'] != 'teacher') { header("Location: dashboard.php"); exit(); }

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $due = $_POST['due_date'];
    $tid = $_SESSION['user_id'];

    // File Upload Logic
    $target_dir = "uploads/";
    $file_name = time() . "_" . basename($_FILES["fileToUpload"]["name"]); // Add timestamp to prevent duplicates
    $target_file = $target_dir . $file_name;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Simple Security: Limit file types
    $allowed = array("pdf", "doc", "docx", "jpg", "png");
    if (in_array($fileType, $allowed)) {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO assignments (teacher_id, title, description, due_date, file_path) 
                    VALUES ('$tid', '$title', '$desc', '$due', '$target_file')";
            $conn->query($sql);
            $message = "Assignment posted successfully!";
        } else {
            $message = "Error uploading file.";
        }
    } else {
        $message = "Invalid file type. Only PDF, DOC, and Images allowed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Post Assignment</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; padding: 2rem; }
        .upload-card { max-width: 600px; margin: auto; background: white; padding: 2.5rem; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: #475569; }
        input, textarea { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; }
        button { width: 100%; padding: 12px; background: #16a34a; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>

<div class="upload-card">
    <h2>Post New Assignment</h2>
    <?php if ($message) echo "<p style='color:green;'>$message</p>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Assignment Title</label>
            <input type="text" name="title" placeholder="e.g. Calculus Homework 1" required>
        </div>

        <div class="form-group">
            <label>Instructions</label>
            <textarea name="description" rows="4"></textarea>
        </div>

        <div class="form-group">
            <label>Due Date</label>
            <input type="date" name="due_date" required>
        </div>

        <div class="form-group">
            <label>Attach File (PDF/Doc/Image)</label>
            <input type="file" name="fileToUpload" required>
        </div>

        <button type="submit">Upload & Post</button>
    </form>
</div>

</body>
</html>