<?php
include 'db.php';
session_start();

// Security: Only students (or admins) should see this
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'student' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

// Fetch all assignments and link with the teacher's name
$sql = "SELECT a.*, u.full_name as teacher_name 
        FROM assignments a 
        JOIN users u ON a.teacher_id = u.id 
        ORDER BY a.due_date ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assignment Gallery | School Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --dark: #1e293b; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; padding: 40px; }
        .container { max-width: 1100px; margin: auto; }
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
        
        .assignment-card { 
            background: white; padding: 20px; border-radius: 16px; 
            border: 1px solid #e2e8f0; position: relative;
            transition: transform 0.2s;
        }
        .assignment-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.05); }
        
        .badge { 
            display: inline-block; padding: 4px 10px; border-radius: 20px; 
            font-size: 11px; font-weight: 700; margin-bottom: 10px; 
        }
        .status-active { background: #dcfce7; color: #166534; }
        .status-late { background: #fee2e2; color: #991b1b; }

        h3 { margin: 0 0 10px 0; color: var(--dark); }
        .meta { font-size: 13px; color: #64748b; margin-bottom: 15px; }
        
        .btn-download { 
            display: block; text-align: center; padding: 10px; 
            background: var(--primary); color: white; text-decoration: none; 
            border-radius: 8px; font-weight: 600; font-size: 14px;
        }
    </style>
</head>
<body>

<div class="container">
    <header style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>Assignment Gallery</h1>
            <p style="color: #64748b;">Download your study materials and homework tasks.</p>
        </div>
        <a href="dashboard.php" style="text-decoration: none; color: var(--primary); font-weight: 600;">← Back to Dashboard</a>
    </header>

    <div class="gallery-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): 
                $today = date('Y-m-d');
                $isLate = ($row['due_date'] < $today);
            ?>
                <div class="assignment-card">
                    <span class="badge <?php echo $isLate ? 'status-late' : 'status-active'; ?>">
                        <?php echo $isLate ? 'OVERDUE' : 'ACTIVE'; ?>
                    </span>
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <div class="meta">
                        <strong>Teacher:</strong> <?php echo htmlspecialchars($row['teacher_name']); ?><br>
                        <strong>Due Date:</strong> <?php echo date('M d, Y', strtotime($row['due_date'])); ?>
                    </div>
                    <p style="font-size: 14px; color: #475569; margin-bottom: 20px;">
                        <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                    </p>
                    <a href="<?php echo $row['file_path']; ?>" class="btn-download" download>
                        📥 Download Resource
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No assignments have been posted yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>