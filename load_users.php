<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { exit("Access Denied"); }

// Fetch Users
$users = $conn->query("SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC");
?>

<div class="glass-card fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin:0;">User Management</h2>
            <p style="color: var(--muted); font-size: 0.85rem;">Review permissions and member activity.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="exportToExcel()" class="btn-mini">📊 Excel</button>
            <a href="register.php" class="btn-action" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center; padding: 10px 20px; background:var(--accent); color:white; border-radius:12px; font-weight:700;">+ New User</a>
        </div>
    </div>

    <div style="margin-bottom: 1.5rem;">
        <input type="text" id="userFilter" placeholder="Search by name, email, or role..." 
               style="width: 100%; padding: 12px 20px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 12px; color: white; outline: none;">
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="text-align: left; color: var(--muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">
                <th style="padding: 15px;">Member</th>
                <th style="padding: 15px;">Role</th>
                <th style="padding: 15px;">Registration</th>
                <th style="padding: 15px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody id="userTableBody">
            <?php if ($users->num_rows > 0): ?>
                <?php while($row = $users->fetch_assoc()): ?>
                <tr class="user-row" id="user-row-<?= $row['id'] ?>" style="border-top: 1px solid var(--border);">
                    <td style="padding: 15px;">
                        <div style="font-weight: 700; color: white;"><?= htmlspecialchars($row['full_name']) ?></div>
                        <div style="font-size: 0.75rem; color: var(--muted);"><?= htmlspecialchars($row['email']) ?></div>
                    </td>

                    <td style="padding: 15px;">
                        <span class="badge <?= strtolower($row['role']) ?>"><?= strtoupper($row['role']) ?></span>
                    </td>

                    <td style="padding: 15px; font-size: 0.85rem; color: var(--muted);">
                        <?= date('M d, Y', strtotime($row['created_at'])) ?>
                    </td>

                    <td style="text-align: right;">
                        <button class="btn-mini" style="color:var(--success);" 
                        onclick="startConversation(<?= $row['id'] ?>, '<?= addslashes($row['full_name']) ?>', '<?= $row['role'] ?>')">
                         💬 Message
                         </button>
    
                        <button class="btn-mini" onclick="editUser(<?= $row['id'] ?>)">Edit</button>
                        <button class="btn-mini" style="color:var(--danger);" onclick="confirmDelete(<?= $row['id'] ?>, '<?= addslashes($row['full_name']) ?>')">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center; padding: 40px; color: var(--muted);">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
    :root { --danger: #f43f5e; --muted: #94a3b8; --border: #334155; --accent: #8b5cf6; }
    .badge { padding: 5px 12px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; letter-spacing: 0.5px; }
    .badge.admin { background: rgba(168, 85, 247, 0.2); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.3); }
    .badge.teacher { background: rgba(99, 102, 241, 0.2); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.3); }
    .badge.student { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
    .btn-mini { background: transparent; border: 1px solid var(--border); color: white; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-size: 0.75rem; margin-left: 5px; transition: 0.2s; }
    .btn-mini:hover { background: rgba(255,255,255,0.05); transform: translateY(-1px); }
    .user-row:hover { background: rgba(255,255,255,0.02); }
</style>

<script>
    // 1. Search Filtering
    document.getElementById('userFilter').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll('.user-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
        });
    });

    // 2. Global Function for Deletion (Fixed ReferenceError)
    window.confirmDelete = function(id, name) {
    if (!confirm(`Are you sure you want to remove ${name}?`)) return;

    const formData = new FormData();
    formData.append('id', id);

    fetch('delete_user.php', {
        method: 'POST',
        body: formData
    })
    .then(async res => {
        const text = await res.text(); // Read as text first to check if empty
        try {
            return JSON.parse(text);
        } catch (err) {
            throw new Error("Server sent invalid data: " + text);
        }
    })
    .then(data => {
        if (data.success) {
            const row = document.getElementById(`user-row-${id}`);
            if (row) {
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            }
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => {
        console.error("Full Error:", err);
        alert("The server encountered a problem. Please check your database connection.");
    });
};
    // 3. Edit Handler
    window.editUser = function(id) {
    // Redirects to the edit page with the specific user ID
    window.location.href = `edit_user.php?id=${id}`;
};
</script>