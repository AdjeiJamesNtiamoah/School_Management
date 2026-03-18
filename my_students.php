<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch teacher's assigned class
$stmt = $conn->prepare("SELECT student_class FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teacher_data = $stmt->get_result()->fetch_assoc();
$assigned_class = $teacher_data['student_class'];

// Only fetch students if a class is assigned
$students = null;
if (!empty($assigned_class)) {
    $student_query = $conn->prepare("SELECT id, full_name, email, profile_pic FROM users WHERE role = 'student' AND student_class = ?");
    $student_query->bind_param("s", $assigned_class);
    $student_query->execute();
    $students = $student_query->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Student List | School Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --dark: #1e293b; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); padding: 40px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .student-table { width: 100%; background: white; border-radius: 15px; border-collapse: collapse; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .student-table th { background: #f1f5f9; padding: 15px; text-align: left; color: #64748b; font-size: 13px; text-transform: uppercase; }
        .student-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; color: var(--dark); vertical-align: middle; }
        .avatar { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; background: #eee; margin-right: 10px; }
        .badge { background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .btn-back { text-decoration: none; color: #64748b; font-weight: 600; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <a href="dashboard.php" class="btn-back">← Back</a>
            <h1 style="margin: 10px 0 0;">Class List: <?php echo htmlspecialchars($assigned_class); ?></h1>
        </div>
        <div class="badge"><?php echo $students->num_rows; ?> Students Total</div>
    </div>

    <table class="student-table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Email Address</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($students->num_rows > 0): ?>
                <?php while($row = $students->fetch_assoc()): ?>
                <tr>
                    <td style="display: flex; align-items: center;">
                        <?php if($row['profile_pic']): ?>
                            <img src="<?php echo $row['profile_pic']; ?>" class="avatar">
                        <?php else: ?>
                            <div class="avatar" style="display:flex; align-items:center; justify-content:center; background: #2563eb; color:white; font-weight:bold;">
                                <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <?php echo $row['full_name']; ?>
                    </td>
                    <td><?php echo $row['email']; ?></td>
                    <td><span class="badge" style="background:#ecfdf5; color:#059669;">Active</span></td>
                    <td><a href="view_student.php?id=<?php echo $row['id']; ?>" style="color: var(--primary); text-decoration:none; font-size: 14px; font-weight:600;">View Profile</a></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding: 40px; color: #94a3b8;">No students registered in this class yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>