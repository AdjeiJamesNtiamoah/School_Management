<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') exit("Unauthorized");

// Fetch current system settings
$settings_res = $conn->query("SELECT * FROM system_settings");
$settings = [];
while($row = $settings_res->fetch_assoc()){
    $settings[$row['setting_key']] = $row['setting_value'];
}

$is_maintenance = isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == 1;
?>

<div class="settings-container fade-in">
    <div class="settings-grid">
        
        <div class="glass-card">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 1.5rem;">
                <div class="icon-box" style="background: var(--danger)20; color: var(--danger);">🛡️</div>
                <h3 style="margin:0;">System Lockdown</h3>
            </div>
            <p style="color: var(--muted); font-size: 0.85rem; line-height: 1.5;">
                Activating Maintenance Mode will redirect all students and teachers to a "System Update" page. Use this for server upgrades or data migration.
            </p>
            
            <div style="margin-top: 2rem; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="display: block; font-weight: 700; font-size: 0.9rem;">Maintenance Status</span>
                    <small style="color: <?= $is_maintenance ? 'var(--danger)' : 'var(--success)' ?>;">
                        ● <?= $is_maintenance ? 'System is currently Locked' : 'System is Live' ?>
                    </small>
                </div>
                <button onclick="toggleMaintenance()" class="btn-action" style="width: auto; background: <?= $is_maintenance ? 'var(--success)' : 'var(--danger)' ?>;">
                    <?= $is_maintenance ? 'Go Live' : 'Lock System' ?>
                </button>
            </div>
        </div>

        <div class="glass-card">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 1.5rem;">
                <div class="icon-box" style="background: var(--success)20; color: var(--success);">📢</div>
                <h3 style="margin:0;">Global Announcement</h3>
            </div>
            <p style="color: var(--muted); font-size: 0.85rem; margin-bottom: 1.5rem;">
                This message will appear on every user's dashboard header.
            </p>
            
            <form id="broadcastForm">
                <input type="text" id="announce_title" placeholder="Announcement Title" class="settings-input" required>
                <textarea id="announce_body" placeholder="Write the details here..." class="settings-input" style="height: 100px; resize: none;" required></textarea>
                <button type="submit" class="btn-action">Broadcast to All</button>
            </form>
        </div>

        <div class="glass-card" style="grid-column: span 2;">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 1.5rem;">
                <div class="icon-box" style="background: var(--accent); color: white;">🎨</div>
                <h3 style="margin:0;">Branding & Identity</h3>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div>
                    <label style="font-size: 0.75rem; color: var(--muted); font-weight: 700;">SCHOOL NAME</label>
                    <input type="text" value="Flawless Intelligence" class="settings-input">
                </div>
                <div>
                    <label style="font-size: 0.75rem; color: var(--muted); font-weight: 700;">ACADEMIC YEAR</label>
                    <input type="text" value="2025/2026" class="settings-input">
                </div>
                <div>
                    <label style="font-size: 0.75rem; color: var(--muted); font-weight: 700;">SYSTEM EMAIL</label>
                    <input type="email" value="admin@flawless.edu" class="settings-input">
                </div>
            </div>
            <button class="btn-action" style="margin-top: 1.5rem; width: 200px;">Save Changes</button>
        </div>

    </div>
</div>

<div class="glass-card">
    <div style="display:flex; align-items:center; gap:15px; margin-bottom:1rem;">
        <div class="icon-box" style="background: var(--accent)20; color: var(--accent);">🔔</div>
        <h3 style="margin:0;">Desktop Alerts</h3>
    </div>
    <p style="font-size:0.8rem; color:var(--muted);">Receive system-level notifications even when the browser is minimized.</p>
    <button onclick="requestNotificationPermission()" class="btn-mini" style="width:100%; margin-top:10px;">
        Grant Permissions
    </button>
</div>
<style>
    .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
    .icon-box { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .settings-input { 
        width: 100%; background: var(--bg); border: 1px solid var(--border); 
        padding: 12px; border-radius: 10px; color: white; margin: 10px 0; outline: none; box-sizing: border-box;
    }
    .settings-input:focus { border-color: #a855f7; }
</style>

<script>
    function toggleMaintenance() {
        if(confirm("Change system visibility?")) {
            fetch('toggle_maintenance.php')
                .then(() => {
                    // Refresh this module only
                    loadPage('load_settings.php', 'System Settings', document.querySelector('[data-page="load_settings.php"]'));
                });
        }
    }

    document.getElementById('broadcastForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // Here you would add the fetch call to save the announcement
        alert("Broadcast sent successfully!");
        this.reset();
    });
</script>