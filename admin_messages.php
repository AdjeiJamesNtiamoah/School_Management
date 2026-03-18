<?php
include 'db.php';
session_start();

// Ensure only Admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$status_msg = "";

// 1. MARK MESSAGES AS READ
// When the admin opens this page, clear their "unread" notifications
$conn->query("UPDATE messages SET status = 'read' WHERE receiver_id = $admin_id AND status = 'unread'");

// 2. HANDLE SENDING REPLY
if (isset($_POST['send_reply'])) {
    $teacher_id = $_POST['teacher_id'];
    $text = mysqli_real_escape_string($conn, $_POST['message_text']);
    
    if (!empty($text) && !empty($teacher_id)) {
        $sql = "INSERT INTO messages (sender_id, receiver_id, message_text) VALUES ($admin_id, $teacher_id, '$text')";
        if ($conn->query($sql)) {
            $status_msg = "Reply sent successfully!";
        }
    }
}

// 3. FETCH TEACHERS (To populate the dropdown)
$teachers = $conn->query("SELECT id, full_name FROM users WHERE role = 'teacher'");

// 4. FETCH ALL CONVERSATIONS
// This grabs the history and labels who sent it and who received it
$history = $conn->query("
    SELECT m.*, 
           sender.full_name as sender_name, 
           receiver.full_name as receiver_name 
    FROM messages m 
    JOIN users sender ON m.sender_id = sender.id 
    JOIN users receiver ON m.receiver_id = receiver.id 
    WHERE m.sender_id = $admin_id OR m.receiver_id = $admin_id 
    ORDER BY m.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Inbox | Flawless Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #020617; --card: #0f172a; --accent: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); --text: #f8fafc; --muted: #94a3b8; }
        body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; padding: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        
        .glass-card { background: var(--card); padding: 30px; border-radius: 24px; border: 1px solid #1e293b; margin-bottom: 25px; }
        
        select, textarea { 
            width: 100%; padding: 14px; background: #1e293b; border: 1px solid #334155; 
            border-radius: 14px; color: white; margin-bottom: 15px; font-family: inherit; box-sizing: border-box;
        }

        .btn-send { 
            background: var(--accent); color: white; border: none; padding: 14px; 
            border-radius: 14px; font-weight: 700; cursor: pointer; width: 100%; transition: 0.3s;
        }
        .btn-send:hover { transform: translateY(-2px); opacity: 0.9; box-shadow: 0 10px 15px -3px rgba(168, 85, 247, 0.4); }

        .chat-container { height: 500px; overflow-y: auto; padding-right: 15px; display: flex; flex-direction: column; }
        
        .bubble { padding: 16px 20px; border-radius: 18px; margin-bottom: 15px; max-width: 80%; position: relative; font-size: 0.95rem; line-height: 1.5; }
        .sent { background: #6366f1; align-self: flex-end; border-bottom-right-radius: 4px; color: white; }
        .received { background: #1e293b; align-self: flex-start; border-bottom-left-radius: 4px; border: 1px solid #334155; }
        
        .meta { font-size: 11px; color: var(--muted); margin-top: 8px; display: block; text-transform: uppercase; font-weight: 600; }
        .sent .meta { color: #e0e7ff; }
        .tag { background: #334155; padding: 2px 8px; border-radius: 6px; font-size: 10px; margin-left: 5px; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        
    </style>
</head>
<body>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h1 style="margin:0;">Admin Inbox</h1>
        <a href="admin_dashboard.php" style="color:var(--muted); text-decoration:none; font-weight:600;">← Admin Dashboard</a>
    </div>

    <?php if($status_msg): ?>
        <div style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 14px; border: 1px solid #10b981; margin-bottom: 20px; text-align:center; font-weight: 600;">
            <?php echo $status_msg; ?>
        </div>
    <?php endif; ?>

    <div class="glass-card">
        <h3 style="margin-top:0;">Compose Reply</h3>
        <form method="POST">
            <select name="teacher_id" required>
                <option value="">Select a Teacher to message...</option>
                <?php while($t = $teachers->fetch_assoc()): ?>
                    <option value="<?php echo $t['id']; ?>"><?php echo $t['full_name']; ?></option>
                <?php endwhile; ?>
            </select>
            <textarea name="message_text" rows="3" placeholder="Type your response here..." required></textarea>
            <button type="submit" name="send_reply" class="btn-send">Send Message</button>
        </form>
    </div>

    <div class="glass-card">
        <h3 style="margin-top:0;">Global Conversation History</h3>
        <div class="chat-container">
            <?php if($history->num_rows > 0): ?>
                <?php while($m = $history->fetch_assoc()): 
                    $is_me = ($m['sender_id'] == $admin_id);
                ?>
                    <div class="bubble <?php echo $is_me ? 'sent' : 'received'; ?>">
                        <strong style="font-size: 0.85rem;">
                            <?php 
                                if($is_me) {
                                    echo "Me (Admin) → to " . htmlspecialchars($m['receiver_name']);
                                } else {
                                    echo htmlspecialchars($m['sender_name']) . " <span class='tag'>Teacher</span>";
                                }
                            ?>
                        </strong><br><br>
                        <?php echo nl2br(htmlspecialchars($m['message_text'])); ?>
                        <span class="meta"><?php echo date('M d, g:i A', strtotime($m['created_at'])); ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; color:var(--muted); margin-top:50px;">Inbox is empty. No messages from teachers yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>