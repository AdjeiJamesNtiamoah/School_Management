<?php
// Simple logic to simulate or fetch real server metrics
$disk_free = disk_free_space("/") / (1024 * 1024 * 1024); // GB
$disk_total = disk_total_space("/") / (1024 * 1024 * 1024); // GB
$disk_usage = round((($disk_total - $disk_free) / $disk_total) * 100);

$load = sys_getloadavg(); // Returns CPU load over 1, 5, and 15 min
$cpu_load = $load[0] * 100 / 8; // Assuming an 8-core processor for visualization
?>

<div class="glass-card fade-in">
    <h3 style="margin-top:0;">Hardware Vitality</h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span style="font-size:0.8rem; font-weight:700;">CPU PROCESSOR</span>
                <span style="font-size:0.8rem; color:var(--accent);"><?= round($cpu_load) ?>%</span>
            </div>
            <div style="width:100%; height:8px; background:var(--bg); border-radius:10px; overflow:hidden;">
                <div style="width:<?= $cpu_load ?>%; height:100%; background:var(--accent); transition:1s;"></div>
            </div>
        </div>
        <div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span style="font-size:0.8rem; font-weight:700;">SERVER STORAGE</span>
                <span style="font-size:0.8rem; color:var(--success);"><?= $disk_usage ?>%</span>
            </div>
            <div style="width:100%; height:8px; background:var(--bg); border-radius:10px; overflow:hidden;">
                <div style="width:<?= $disk_usage ?>%; height:100%; background:var(--success); transition:1s;"></div>
            </div>
        </div>
    </div>
</div>