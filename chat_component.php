<?php
// chat_component.php
include 'db.php';
// We don't call session_start() here because it's already in admin_dashboard.php

$u_id = $_SESSION['user_id'];

// Get the list of people the admin has chatted with recently
$recent_list = $conn->query("SELECT DISTINCT u.id, u.full_name FROM users u 
    JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
    WHERE u.id != $u_id AND (m.sender_id = $u_id OR m.receiver_id = $u_id) LIMIT 5");
?>

<div style="padding: 10px; border-bottom: 1px solid var(--border);">
    <select onchange="window.location.href='admin_dashboard.php?receiver_id='+this.value" style="width:100%; padding:8px; background:var(--bg); color:white; border:none; border-radius:5px;">
        <option value="">Select User to Chat</option>
        <?php while($row = $recent_list->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>" <?= ($active_id == $row['id']) ? 'selected' : '' ?>>
                <?= $row['full_name'] ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<div id="chatBox" style="flex: 1; overflow-y: auto; padding: 15px; display: flex; flex-direction: column; gap: 10px; background: rgba(0,0,0,0.05);">
    <?php if($active_id == 0): ?>
        <p style="text-align:center; color:var(--muted); margin-top:50px;">Select a user to view messages</p>
    <?php endif; ?>
</div>

<?php if($active_id > 0): ?>
<form id="dashMsgForm" style="padding: 10px; border-top: 1px solid var(--border);">
    <div style="display: flex; gap: 5px;">
        <input type="text" id="dashMsgInput" placeholder="Reply..." style="flex: 1; padding: 10px; border-radius: 5px; background: var(--bg); border: 1px solid var(--border); color: white;">
        <button type="submit" style="background: var(--accent); border: none; padding: 0 15px; border-radius: 5px; color: white; cursor: pointer;">Send</button>
    </div>
</form>
<?php endif; ?>

<script>
// Reuse your existing AJAX logic here
const dashActiveId = <?= $active_id ?>;
if(dashActiveId > 0) {
    setInterval(() => {
        fetch(`fetch_messages.php?receiver_id=${dashActiveId}`)
            .then(res => res.text())
            .then(data => {
                const box = document.getElementById('chatBox');
                if(box.innerHTML !== data) { box.innerHTML = data; box.scrollTop = box.scrollHeight; }
            });
    }, 2000);
}

document.getElementById('dashMsgForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const msg = document.getElementById('dashMsgInput').value;
    fetch('send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `receiver_id=${dashActiveId}&message=${encodeURIComponent(msg)}`
    }).then(() => { document.getElementById('dashMsgInput').value = ''; });
});
</script>