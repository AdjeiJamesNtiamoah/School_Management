<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user_id is actually set in the session
if (!isset($_SESSION['user_id'])) {
    exit("<div style='color:red; padding:20px;'>Error: Session expired. Please log in again.</div>");
}

$u_id = $_SESSION['user_id'];

// Use a template-safe approach or ensure $u_id is an integer
$u_id = (int)$u_id; 

$result = $conn->query("SELECT * FROM users WHERE id = $u_id");

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    exit("<div style='color:red; padding:20px;'>Error: User record not found.</div>");
}

$avatar = !empty($user['profile_pic']) ? $user['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name']) . '&background=a855f7&color=fff';
?>

<div class="profile-container fade-in">
    <div style="display: grid; grid-template-columns: 300px 1fr; gap: 30px;">
        
        <div class="glass-card" style="text-align: center;">
            <div class="avatar-upload-wrapper">
                <img src="<?= $avatar ?>" id="avatar-preview" class="main-avatar">
                <label for="pic-upload" class="edit-overlay">📸</label>
                <input type="file" id="pic-upload" style="display:none;" accept="image/*" onchange="previewImage(this)">
            </div>
            
            <h2 style="margin: 15px 0 5px;"><?= htmlspecialchars($user['full_name']) ?></h2>
            <p style="color:var(--muted); font-size:0.8rem; margin-bottom: 20px;"><?= strtoupper($user['role']) ?> ACCESS LEVEL</p>
            
            <div class="info-pill">Joined: <?= date('M Y', strtotime($user['created_at'])) ?></div>
        </div>

        <div class="glass-card">
            <h3 style="margin-top:0;">Account Security</h3>
            <p style="color:var(--muted); font-size:0.85rem; margin-bottom:25px;">Update your password regularly to maintain a flawless system.</p>
            
            <form id="profileForm">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label class="input-label">Full Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['full_name']) ?>" class="settings-input">
                    </div>
                    <div>
                        <label class="input-label">Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="settings-input">
                    </div>
                </div>

                <hr style="border:0; border-top: 1px solid var(--border); margin: 25px 0;">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="input-label">New Password</label>
                        <input type="password" id="new_pass" name="new_pass" placeholder="Leave blank to keep current" class="settings-input">
                    </div>
                    <div>
                        <label class="input-label">Confirm Password</label>
                        <input type="password" id="confirm_pass" placeholder="Confirm new password" class="settings-input">
                    </div>
                </div>

                <button type="submit" class="btn-action" style="margin-top: 25px; width: 220px;">Update Identity</button>
            </form>
            <div id="status-msg" style="margin-top: 15px; font-size: 0.85rem;"></div>
        </div>
    </div>
</div>

<style>
    .avatar-upload-wrapper { position: relative; width: 150px; height: 150px; margin: 0 auto; }
    .main-avatar { width: 100%; height: 100%; border-radius: 30px; object-fit: cover; border: 4px solid var(--panel); box-shadow: 0 10px 20px rgba(0,0,0,0.3); }
    .edit-overlay { position: absolute; bottom: -5px; right: -5px; background: var(--accent); width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid var(--panel); transition: 0.3s; }
    .edit-overlay:hover { transform: scale(1.1); }
    
    .input-label { font-size: 0.75rem; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-pill { background: rgba(168, 85, 247, 0.1); color: #a855f7; display: inline-block; padding: 6px 15px; border-radius: 10px; font-weight: 800; font-size: 0.75rem; }
</style>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { document.getElementById('avatar-preview').src = e.target.result; }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const nPass = document.getElementById('new_pass').value;
        const cPass = document.getElementById('confirm_pass').value;
        const status = document.getElementById('status-msg');

        if(nPass !== cPass) {
            status.innerHTML = "❌ Passwords do not match!";
            status.style.color = "var(--danger)";
            return;
        }

        const formData = new FormData(this);
        const pic = document.getElementById('pic-upload').files[0];
        if(pic) formData.append('profile_pic', pic);

        fetch('update_profile.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                status.innerHTML = data.success ? "✅ Profile Updated Successfully!" : "❌ " + data.error;
                status.style.color = data.success ? "var(--success)" : "var(--danger)";
                if(data.success && pic) {
                    // Refresh sidebar avatar if you have one
                    window.location.reload(); 
                }
            });
    });
</script>