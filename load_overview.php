<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Fetch Real-Time Stats
$total_students = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'student'")->fetch_assoc()['c'];
$total_staff = $conn->query("SELECT COUNT(*) as c FROM users WHERE role IN ('teacher', 'admin')")->fetch_assoc()['c'];

// Count active chats (distinct conversations in the last 24 hours)
$active_chats_query = "SELECT COUNT(*) as c FROM (
    SELECT sender_id, receiver_id 
    FROM messages 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    GROUP BY LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id)
) as active_conversations";

$active_chats = $conn->query("SELECT COUNT(DISTINCT conversation_id) as c FROM messages WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetch_assoc()['c'] ?? 0;

// 2. Fetch daily login counts for the chart
$chart_data = $conn->query("
    SELECT DATE(created_at) as day, COUNT(*) as count 
    FROM system_logs 
    WHERE action_text LIKE '%session%' 
    GROUP BY day ORDER BY day DESC LIMIT 7
")->fetch_all(MYSQLI_ASSOC);

$days = json_encode(array_column(array_reverse($chart_data), 'day'));
$counts = json_encode(array_column(array_reverse($chart_data), 'count'));
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="fade-in">
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px;">
        <div class="stat-box">
            <small>Total Students</small>
            <h2><?= number_format($total_students) ?></h2>
            <div class="stat-label student">Active Learners</div>
        </div>
        <div class="stat-box">
            <small>Total Staff</small>
            <h2><?= number_format($total_staff) ?></h2>
            <div class="stat-label staff">Faculty & Admin</div>
        </div>
        <div class="stat-box">
            <small>Active Chats</small>
            <h2><?= number_format($active_chats) ?></h2>
            <div class="stat-label chats">Last 24 Hours</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
        <div class="glass-card">
            <h3 style="margin-top:0;">Usage Velocity</h3>
            <div style="height: 300px; width: 100%;">
                <canvas id="usageChart"></canvas>
            </div>
        </div>

        <div class="glass-card" style="background: var(--sidebar);">
            <h3 style="margin-top:0;">Secure Actions</h3>
            <button class="action-row" onclick="loadPage('load_users.php', 'Users', this)">
                <span>👥 Audit Users</span>
                <small>Verify access logs</small>
            </button>
            <button class="action-row" style="border-color: var(--danger)40;" onclick="if(confirm('Wipe system cache?')) { /* Add logic */ }">
                <span style="color: var(--danger);">🧹 Purge Cache</span>
                <small>Clear temporary files</small>
            </button>
            
            <div style="margin-top: 2rem; padding: 15px; border-radius: 12px; border: 1px solid var(--border); font-size: 0.8rem;">
                <span style="color: var(--muted);">System Health:</span><br>
                <strong style="color:var(--success);">● Optimized</strong>
            </div>
        </div>
    </div>
</div>

<style>
    /* New Stat Box Styling */
    .stat-box { 
        background: var(--panel); 
        padding: 20px; 
        border-radius: 20px; 
        border: 1px solid var(--border);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .stat-box small { color: var(--muted); font-weight: 600; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.5px; }
    .stat-box h2 { margin: 10px 0; font-size: 2rem; font-weight: 800; color: white; }
    .stat-label { font-size: 0.75rem; font-weight: 700; display: inline-block; padding: 4px 10px; border-radius: 6px; }
    
    .stat-label.student { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .stat-label.staff { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
    .stat-label.chats { background: rgba(168, 85, 247, 0.1); color: #a855f7; }

    .action-row { 
        width: 100%; text-align: left; background: transparent; border: 1px solid var(--border); 
        padding: 15px; border-radius: 12px; color: white; cursor: pointer; margin-bottom: 10px;
        display: flex; flex-direction: column; transition: 0.2s;
    }
    .action-row:hover { background: rgba(255,255,255,0.05); border-color: #a855f7; }
</style>

<script>
    // Initialize Analytics Chart
    const ctx = document.getElementById('usageChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= $days ?>,
            datasets: [{
                label: 'System Access',
                data: <?= $counts ?>,
                borderColor: '#a855f7',
                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
            }
        }
    });
</script>