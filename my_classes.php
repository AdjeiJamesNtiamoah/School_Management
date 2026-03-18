<?php
include 'db.php';
session_start();

if ($_SESSION['role'] != 'teacher') { 
    header("Location: dashboard.php"); 
    exit(); 
}

// Fetch all students. 
// In a full system, you'd filter by 'class_id' assigned to this teacher.
$sql = "SELECT id, full_name, email FROM users WHERE role = 'student' ORDER BY full_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Classes | School Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --dark: #1e293b; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; margin: 0; padding: 40px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        h2 { color: var(--dark); margin: 0; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; background: #f1f5f9; padding: 15px; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 14px; }
        
        .avatar { width: 35px; height: 35px; background: #e2e8f0; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; color: #64748b; margin-right: 10px; }
        
        .btn-small { padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.2s; }
        .btn-view { background: #eff6ff; color: #2563eb; margin-right: 5px; }
        .btn-view:hover { background: #2563eb; color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h2>My Students</h2>
            <p style="color: #64748b; font-size: 14px; margin-top: 5px;">Total Students: <?php echo $result->num_rows; ?></p>
        </div>
        <a href="dashboard.php" style="text-decoration: none; color: var(--primary); font-weight: 600; font-size: 14px;">← Back</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Email Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td style="display: flex; align-items: center;">
                        <div class="avatar"><?php echo strtoupper(substr($row['full_name'], 0, 1)); ?></div>
                        <?php echo htmlspecialchars($row['full_name']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <a href="manage_grades.php?id=<?php echo $row['id']; ?>" class="btn-small btn-view">Add Grades</a>
                        <a href="student_remarks.php?id=<?php echo $row['id']; ?>" class="btn-small btn-view" style="color: #db2777; background: #fdf2f8;">Add Remark</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" style="text-align: center; color: #94a3b8;">No students found in this class.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>