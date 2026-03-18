<?php
include 'db.php';
session_start();

// 1. SECURITY & SESSION CHECK
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$u_id = $_SESSION['user_id'];
$u_role = $_SESSION['role'];
$u_name = $_SESSION['full_name'] ?? 'User';

// 2. DYNAMIC CONTENT FILTER (Role-based)
if ($u_role === 'admin') {
    $dashboard_link = "admin_dashboard.php";
    $recipient_query = "SELECT id, full_name, role FROM users WHERE id != $u_id";
} elseif ($u_role === 'teacher') {
    $dashboard_link = "teacher_dashboard.php";
    $recipient_query = "SELECT id, full_name, role FROM users WHERE id != $u_id AND role IN ('admin', 'student')";
} else {
    $dashboard_link = "student_dashboard.php";
    $recipient_query = "SELECT id, full_name, role FROM users WHERE id != $u_id AND role IN ('admin', 'teacher')";
}

$recipients = $conn->query($recipient_query . " ORDER BY role ASC, full_name ASC");

// 3. GET ACTIVE CHAT ID (Ensuring $_GET is used correctly)
$active_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;

// 4. FETCH RECENT CHATS FOR SIDEBAR (To show the 'hi' message seen in your screenshot)
$recent_chats = $conn->query("SELECT DISTINCT u.id, u.full_name, u.role, 
    (SELECT message_text FROM messages WHERE (sender_id = u.id AND receiver_id = $u_id) OR (sender_id = $u_id AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_msg
    FROM users u 
    JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
    WHERE u.id != $u_id AND (m.sender_id = $u_id OR m.receiver_id = $u_id)
    LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages | Flawless</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #020617; --sidebar: #0f172a; --card: #1e293b; --text: #f8fafc; --muted: #94a3b8; --border: #334155; --accent: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); }
        body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        /* Layout */
        .sidebar { width: 260px; background: var(--sidebar); padding: 30px 20px; border-right: 1px solid var(--border); }
        .sidebar-brand { font-size: 1.5rem; font-weight: 800; background: var(--accent); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 40px; }
        .nav-links a { display: block; padding: 14px 18px; color: var(--muted); text-decoration: none; font-weight: 600; border-radius: 12px; margin-bottom: 8px; }
        .nav-links a.active { background: var(--accent); color: white; }

        .main { flex: 1; display: flex; flex-direction: column; padding: 30px; }
        .chat-grid { display: grid; grid-template-columns: 320px 1fr; gap: 25px; flex: 1; min-height: 0; }
        .glass-card { background: var(--card); border-radius: 24px; border: 1px solid var(--border); display: flex; flex-direction: column; overflow: hidden; }
        
        /* Chat Components */
        .chat-list-item { padding: 15px; border-bottom: 1px solid var(--border); cursor: pointer; transition: 0.2s; }
        .chat-list-item:hover { background: rgba(255,255,255,0.05); }
        .chat-list-item.active { background: rgba(168, 85, 247, 0.1); border-left: 4px solid #a855f7; }
        
        .chat-history { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; }
        .compose-area { padding: 20px; border-top: 1px solid var(--border); }
        textarea { width: 100%; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 10px; color: white; resize: none; font-family: inherit; }
        
        .btn { padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; color: white; }
    
        /* Inside your <style> tag in messages.php */

.chat-history {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 20px;
}

.bubble {
    max-width: 75%;
    padding: 12px 16px;
    border-radius: 18px;
    font-size: 0.95rem;
    line-height: 1.4;
    position: relative;
    word-wrap: break-word;
}

/* Messages SENT by you (Aligned Right) */
.sent {
    align-self: flex-end;
    background: var(--accent); /* This uses your purple gradient */
    color: white;
    border-bottom-right-radius: 4px;
}

/* Messages RECEIVED by you (Aligned Left) */
.received {
    align-self: flex-start;
    background: #2d3748;
    color: #f8fafc;
    border: 1px solid var(--border);
    border-bottom-left-radius: 4px;
}

.timestamp {
    display: block;
    font-size: 0.65rem;
    margin-top: 5px;
    opacity: 0.7;
    text-align: right;
}
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">FLAWLESS</div>
    <nav class="nav-links">
        <a href="<?= $dashboard_link ?>">🏠 Dashboard</a>
        <a href="messages.php" class="active">💬 Messages</a>
        <?php if($u_role === 'admin'): ?>
            <a href="manage_users.php">👥 Manage Users</a>
        <?php endif; ?>
        <a href="logout.php" style="color:#f43f5e; margin-top: 50px;">🚪 Logout</a>
    </nav>
</aside>

<main class="main">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1 style="margin:0;">Chat Room</h1>
            <small style="color:var(--muted)">Signed in as <?= $u_name ?> (<?= ucfirst($u_role) ?>)</small>
        </div>
        <div>
            <button class="btn" style="background: #10b981;">📹 Video Call</button>
            <button class="btn" style="background: #f43f5e; margin-left: 10px;">🗑️ Clear</button>
        </div>
    </div>

    <div class="chat-grid">
        <div class="glass-card">
            <div style="padding: 15px; border-bottom: 1px solid var(--border);">
                <select onchange="window.location.href='messages.php?receiver_id='+this.value" style="width:100%; padding:10px; border-radius:8px; background:var(--bg); color:white; border:1px solid var(--border);">
                    <option value="">+ Start New Chat</option>
                    <?php while($r = $recipients->fetch_assoc()): ?>
                        <option value="<?= $r['id'] ?>" <?= ($active_id == $r['id']) ? 'selected' : '' ?>>
                            <?= $r['full_name'] ?> (<?= ucfirst($r['role']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="overflow-y: auto;">
                <?php if($recent_chats->num_rows > 0): ?>
                    
                    <?php while($chat = $recent_chats->fetch_assoc()): ?>
                        <div class="chat-list-item <?= ($active_id == $chat['id']) ? 'active' : '' ?>" 
                            onclick="window.location.href='messages.php?receiver_id=<?= $chat['id'] ?>'">
                         <div style="font-weight: 700;"><?= $chat['full_name'] ?></div>
                        <small style="color: #a855f7;"><?= strtoupper($chat['role']) ?></small>
                        </div>
                <?php endwhile; ?>
                    
                <p style="padding: 20px; color: var(--muted); text-align: center;">No recent chats</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="glass-card">
            <?php if($active_id > 0): ?>
                <div class="chat-history" id="chatBox">
                    </div>
                <div class="compose-area">
                    <form id="liveMsgForm">
                        <input type="hidden" id="receiver_id" value="<?= $active_id ?>">
                        <textarea id="msgInput" placeholder="Type your message..." required></textarea>
                        <button type="submit" style="margin-top:10px; width:100%; padding:12px; background:var(--accent); border:none; color:white; border-radius:10px; font-weight:800; cursor:pointer;">Send 🚀</button>
                    </form>
                </div>
            <?php else: ?>
                <div style="display:flex; align-items:center; justify-content:center; height:100%; color:var(--muted); text-align: center; padding: 20px;">
                    <h3>Select a user to start a live chat</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
const activeId = <?= $active_id ?>;
const chatBox = document.getElementById('chatBox');

// 1. Live Fetching Function
function fetchMessages() {
    if(activeId === 0) return;
    fetch(`fetch_messages.php?receiver_id=${activeId}`)
        .then(res => res.text())
        .then(data => {
            if(chatBox.innerHTML !== data) {
                chatBox.innerHTML = data;
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
}

// 2. Refresh interval (every 2 seconds)
if(activeId > 0) {
    setInterval(fetchMessages, 2000);
    fetchMessages(); // Initial load
}

// 3. Send Message via AJAX (No Refresh)
document.getElementById('liveMsgForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const message = document.getElementById('msgInput').value;
    
    fetch('send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `receiver_id=${activeId}&message=${encodeURIComponent(message)}`
    }).then(() => {
        document.getElementById('msgInput').value = ''; // Clear input
        fetchMessages(); // Update chat immediately
    });
});
</script>
<script>
// 1. Get the ID from the URL (provided by the PHP variable at the top of the page)
const activeId = <?= $active_id ?>; 

function fetchMessages() {
    if(activeId === 0) return; 
    
    // 2. Ask fetch_messages.php for the data belonging to this specific ID
    fetch(`fetch_messages.php?receiver_id=${activeId}`)
        .then(res => res.text())
        .then(data => {
            const chatBox = document.getElementById('chatBox');
            if(chatBox.innerHTML !== data) {
                chatBox.innerHTML = data;
                chatBox.scrollTop = chatBox.scrollHeight; 
            }
        });
}

// 3. Tell the browser to check for new messages every 2 seconds
if(activeId > 0) {
    setInterval(fetchMessages, 2000);
    fetchMessages(); 
}
</script>
</body>
</html>