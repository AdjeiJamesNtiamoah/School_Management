<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$u_id = $_SESSION['user_id'];

// 1. DATA FETCHING (Now includes student_class)
$user = $conn->query("SELECT * FROM users WHERE id = $u_id")->fetch_assoc();

// FIX: Added 'student_class' to this query so the table can see it
$students = $conn->query("SELECT id, full_name, email, student_class, created_at FROM users WHERE role = 'student' ORDER BY full_name ASC");

// 2. COUNTS & STATUS
$student_count = $students->num_rows;
$unread_msg = $conn->query("SELECT COUNT(id) as total FROM messages WHERE receiver_id = $u_id AND status = 'unread'")->fetch_assoc()['total'];

// 3. TIMECLOCK STATUS
$last_log = $conn->query("SELECT * FROM attendance_logs WHERE user_id = $u_id ORDER BY id DESC LIMIT 1")->fetch_assoc();
$is_clocked_in = ($last_log && $last_log['clock_out'] === NULL);

$user_photo = !empty($user['profile_pic']) ? $user['profile_pic'] : 'uploads/default.png';

?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Management | Flaw</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #020617; --sidebar: #0f172a; --card: #1e293b; --text: #f8fafc; --muted: #94a3b8; --border: #334155; --accent: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); }
        [data-theme="light"] { --bg: #f1f5f9; --sidebar: #ffffff; --card: #ffffff; --text: #0f172a; --muted: #64748b; --border: #e2e8f0; }

        body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; transition: 0.3s; }
        .sidebar { width: 260px; background: var(--sidebar); padding: 30px 20px; border-right: 1px solid var(--border); display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-profile { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid var(--border); }
        .sidebar-img { width: 70px; height: 70px; border-radius: 18px; object-fit: cover; border: 2px solid var(--border); }
        .nav-links a { display: block; padding: 12px 15px; color: var(--muted); text-decoration: none; font-weight: 600; border-radius: 12px; margin-bottom: 8px; cursor: pointer; transition: 0.2s; }
        .nav-links a.active, .nav-links a:hover { background: var(--accent); color: white; }
        .main { flex: 1; padding: 40px; overflow-y: auto; }
        .view-section { display: none; animation: fadeIn 0.4s ease; }
        .view-section.active { display: block; }
        .glass-card { background: var(--card); padding: 25px; border-radius: 20px; border: 1px solid var(--border); margin-top: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: var(--muted); font-size: 0.8rem; text-transform: uppercase; padding: 15px; border-bottom: 1px solid var(--border); }
        td { padding: 15px; border-bottom: 1px solid var(--border); font-size: 0.95rem; }
        .badge { padding: 4px 10px; border-radius: 8px; font-weight: 700; font-size: 0.8rem; }
        .clock-btn { padding: 20px 40px; border-radius: 50px; border: none; font-weight: 800; font-size: 1.2rem; cursor: pointer; transition: 0.3s; color: white; }
        .clock-in { background: #10b981; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); }
        .clock-out { background: #f43f5e; box-shadow: 0 10px 20px rgba(244, 63, 94, 0.3); }
        
        /* Modal Styles */
        .modal-overlay { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.8); backdrop-filter: blur(8px); justify-content: center; align-items: center; }
        .modal-content { width: 100%; max-width: 450px; background: var(--card); padding: 35px; border-radius: 24px; border: 1px solid var(--border); animation: modalSlide 0.3s ease-out; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: var(--muted); font-size: 0.8rem; font-weight: 700; margin-bottom: 8px; text-transform: uppercase; }
        .form-group input, .form-group select { width: 100%; padding: 14px; background: var(--bg); border: 1px solid var(--border); border-radius: 12px; color: var(--text); box-sizing: border-box; }
        .btn-primary { background: var(--accent); color: white; border: none; padding: 14px 25px; border-radius: 12px; font-weight: 700; cursor: pointer; width: 100%; }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        @media print {
    body { background: white !important; color: black !important; }
    .sidebar, .theme-btn, #link-reports, button, select { display: none !important; }
    .main { padding: 0 !important; width: 100% !important; }
    .glass-card { border: none !important; box-shadow: none !important; }
    .view-section { display: none !important; }
    #view-reports { display: block !important; width: 100% !important; }
}


    /* 1. ROOT & THEME */
    :root { 
        --bg: #020617; --sidebar: #0f172a; --card: #1e293b; --text: #f8fafc; 
        --muted: #94a3b8; --border: #334155; 
        --accent: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); 
    }
    [data-theme="light"] { 
        --bg: #f1f5f9; --sidebar: #ffffff; --card: #ffffff; --text: #0f172a; 
        --muted: #64748b; --border: #e2e8f0; 
    }

    /* 1. Ensure the main container takes up the full screen but doesn't grow */
.main-viewport {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 100vh; /* Exactly the height of the screen */
    overflow: hidden; /* Prevents the whole page from bouncing */
}

/* 2. This is your 'Workspace'. It needs to handle the scrolling. */
.view-content {
    flex: 1;
    overflow-y: auto; /* <--- THIS ENABLES SCROLLING */
    padding: 30px 40px;
    background: var(--bg);
}

/* 3. Ensure the individual sections don't break the scroll */
.view-section {
    display: none;
    min-height: min-content; /* Allows it to expand as much as needed */
}

.view-section.active {
    display: block; /* Use block instead of flex if content is purely vertical */
}

/* Optional: Make the scrollbar look professional */
.view-content::-webkit-scrollbar {
    width: 8px;
}
.view-content::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 10px;
}

    /* 5. SCROLLBARS */
    .contact-list::-webkit-scrollbar, .message-stream::-webkit-scrollbar { width: 5px; }
    .contact-list::-webkit-scrollbar-thumb, .message-stream::-webkit-scrollbar-thumb { 
        background: #374151; border-radius: 10px; 
    }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
#view-messages {
    height: 100%; /* Fill the main content area */
    display: none;
    flex-direction: column;
}

#view-messages.active {
    display: flex !important;
    flex: 1;
    height: 80%;
    padding: 0; /* Messaging looks better edge-to-edge */
}

#messages-container {
    flex: 1;
    display: flex;
    overflow: hidden; /* Keeps the internal scrollbars working */
}

/* MESSAGES SPECIFIC */
        #messages-ajax-content { flex: 1; display: flex; height: 100%; overflow: hidden; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        .glass-card { 
            background: var(--card); padding: 25px; border-radius: 20px; 
            border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
        }

        .contact-item { padding: 15px; border-bottom: 1px solid #222d34; cursor: pointer; transition: 0.2s; }
    .contact-item:hover { background: #202c33; }
    
    .chat-input-area { padding: 10px 20px; background: #202c33; display: flex; align-items: center; gap: 15px; }
    #msgText { flex: 1; background: #2a3942; border: none; padding: 12px; border-radius: 8px; color: white; outline: none; }

</style>


</head>
<body>

<aside class="sidebar">
    <div class="sidebar-profile">
        <img src="<?php echo $user_photo; ?>" class="sidebar-img">
        <h4 style="margin: 10px 0 0;"><?php echo $user['full_name']; ?></h4>
        <small style="color: var(--muted); font-weight: 700;">TEACHER</small>
    </div>
    <nav class="nav-links">
        <a onclick="showView('overview')" id="link-overview" class="active">Overview</a>
        <a onclick="showView('students')" id="link-students">Students</a>
        <a onclick="showView('attendance')" id="link-attendance">Attendance</a>
        <a onclick="showView('reports')" id="link-reports">Reports</a>
        <a onclick="showView('timeclock')" id="link-timeclock">Timeclock</a>
        <a id="link-messages" onclick="showView('messages')">
            <i class="fas fa-comment-dots"></i> Messages 
            <?php if($unread_msg > 0): ?><span style="background:white; color:#6366f1; padding:2px 8px; border-radius:10px; font-size:0.7rem; margin-left:auto;"><?= $unread_msg ?></span><?php endif; ?>
        </a>
        <a href="profile.php">Settings</a>
        <a href="logout.php" style="margin-top: auto; color: #f43f5e;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        
    </nav>
</aside>

<main class="main">
    <?php 
// 1. We check if 'msg' exists, if not, $current_msg stays null
$current_msg = $_GET['msg'] ?? null; 

if($current_msg): 
?>
    <div class="status-alert" id="autoAlert" style="position: fixed; top: 20px; right: 20px; z-index: 10000; padding: 15px 25px; border-radius: 12px; font-weight: 700; transition: all 0.5s ease;
        <?php echo in_array($current_msg, ['student_added', 'attendance_saved', 'clocked_in']) ? 'background: #10b981; color: white;' : 'background: #f43f5e; color: white;'; ?>">
        
        <?php 
            $msgs = [
                'student_added'    => "✓ New student registered!",
                'attendance_saved' => "✓ Attendance updated successfully!",
                'clocked_in'       => "✓ Clocked in!",
                'clocked_out'      => "✓ Clocked out!",
                'email_exists'     => "⚠ Email already exists.",
                'pass_success'     => "✓ Password updated!",
                'wrong_pass'       => "⚠ Incorrect current password."
            ];
            // Display the mapped message, or the raw message if not in our list
            echo $msgs[$current_msg] ?? "Update Successful";
        ?>
    </div>

    <script>
        // Auto-hide logic
        setTimeout(() => {
            const alert = document.getElementById('autoAlert');
            if(alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000);
    </script>
<?php endif; ?>

    <script>
        // Wait 4 seconds, then fade out and remove the alert
        setTimeout(() => {
            const alert = document.getElementById('status-alert');
            if(alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500); // Remove from DOM after fade
            }
        }, 4000); 
    </script>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:40px;">
        <h1 id="page-title">Dashboard Overview</h1>
        <div style="cursor:pointer; background:var(--card); padding:10px 20px; border-radius:50px; font-weight:700; font-size:0.8rem; border:1px solid var(--border);" onclick="toggleTheme()">🌓 THEME</div>
    </div>

   <div id="view-overview" class="view-section active">
    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:20px;">
        <div class="glass-card">
            <p style="color:var(--muted); font-size:0.8rem; font-weight:700; margin:0;">TOTAL STUDENTS</p>
            <h2 style="font-size:2.5rem; margin:10px 0 0;"><?php echo $student_count; ?></h2>
        </div>
        <div class="glass-card">
            <p style="color:var(--muted); font-size:0.8rem; font-weight:700; margin:0;">STATUS</p>
            <h2 style="font-size:1.8rem; margin:10px 0 0; color:<?php echo $is_clocked_in ? '#10b981' : '#f43f5e'; ?>;"><?php echo $is_clocked_in ? 'Clocked In' : 'Clocked Out'; ?></h2>
        </div>
        <div class="glass-card">
            <p style="color:var(--muted); font-size:0.8rem; font-weight:700; margin:0;">MESSAGES</p>
            <h2 style="font-size:2.5rem; margin:10px 0 0;"><?php echo $unread_msg; ?></h2>
        </div>
    </div>
</div>

<section id="view-messages" class="view-section">
            <div id="messages-ajax-content">
                </div>
        </section>

    <div id="view-students" class="view-section">
        <div class="glass-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <div style="display:flex; gap:10px;">
                    <input type="text" id="studentSearch" placeholder="🔍 Search name..." onkeyup="combinedFilter()" style="padding:10px; border-radius:10px; border:1px solid var(--border); background:var(--bg); color:var(--text);">
                    <select id="classFilter" onchange="combinedFilter()" style="padding:10px; border-radius:10px; border:1px solid var(--border); background:var(--bg); color:var(--text);">
                        <option value="all">All Classes</option>
                        <option value="Not Assigned">Not Assigned</option>
                        <option value="JHS 1">JHS 1</option><option value="JHS 2">JHS 2</option><option value="JHS 3">JHS 3</option>
                        <option value="SHS 1">SHS 1</option><option value="SHS 2">SHS 2</option><option value="SHS 3">SHS 3</option>
                    </select>
                </div>
                <button onclick="openModal()" class="btn-primary" style="width:auto;">+ Add Student</button>
            </div>
            <table id="studentTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Last Login</th> <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $students->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight:600;"><?php echo $row['full_name']; ?></td>
                        <td><span class="badge"><?php echo $row['student_class'] ?? 'Not Assigned'; ?></span></td>
                        
                        <td style="color:var(--muted); font-size:0.85rem;">
                            <?php 
                                if (!empty($row['last_login'])) {
                                    echo date('M d, g:i A', strtotime($row['last_login']));
                                } else {
                                    echo "Never";
                                }
                            ?>
                        </td>

                        <td>
                            <div style="display:flex; gap:10px;">
                                <a href="edit_student.php?id=<?php echo $row['id']; ?>" style="color:#a855f7; font-weight:700; text-decoration:none;">Edit</a>
                                <a href="delete_student.php?id=<?php echo $row['id']; ?>" style="color:#f43f5e; font-weight:700; text-decoration:none;">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="view-timeclock" class="view-section">
        <div class="glass-card" style="text-align:center; padding:60px 20px;">
            <h1 id="liveClock" style="font-size:4rem; margin:0 0 40px;">00:00:00</h1>
            <form action="process_timeclock.php" method="POST">
                <button type="submit" name="action" value="<?php echo $is_clocked_in ? 'clock_out' : 'clock_in'; ?>" class="clock-btn <?php echo $is_clocked_in ? 'clock-out' : 'clock-in'; ?>">
                    <?php echo $is_clocked_in ? 'CLOCK OUT NOW' : 'CLOCK IN FOR TODAY'; ?>
                </button>
            </form>
        </div>
    </div>
    <div id="view-attendance" class="view-section">
    <div class="glass-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2>Take Attendance (<?php echo date('M d, Y'); ?>)</h2>
            <select id="attendanceClassFilter" onchange="filterAttendanceTable()" style="padding:10px; border-radius:10px; background:var(--bg); color:var(--text); border:1px solid var(--border);">
                <option value="JHS 1">JHS 1</option>
                <option value="JHS 2">JHS 2</option>
                <option value="JHS 3">JHS 3</option>
                <option value="SHS 1">SHS 1</option>
                <option value="SHS 2">SHS 2</option>
                <option value="SHS 3">SHS 3</option>
            </select>
        </div>

        <form action="save_attendance.php" method="POST">
            <table id="attendanceTable">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Reset the pointer of the students query to loop through them again
                    $students->data_seek(0); 
                    while($row = $students->fetch_assoc()): 
                    ?>
                    <tr class="attendance-row" data-class="<?php echo $row['student_class']; ?>">
                        <td><?php echo $row['full_name']; ?></td>
                        <td><span class="badge"><?php echo $row['student_class']; ?></span></td>
                        <td>
                            <input type="hidden" name="student_ids[]" value="<?php echo $row['id']; ?>">
                            <select name="status[]" style="padding:8px; border-radius:8px; background:var(--bg); color:var(--text); border:1px solid var(--border);">
                                <option value="Present">✅ Present</option>
                                <option value="Absent">❌ Absent</option>
                                <option value="Late">⏰ Late</option>
                            </select>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" class="btn-primary" style="margin-top:20px; width:auto;">💾 Save Attendance</button>
        </form>
    </div>
</div>

<div id="view-reports" class="view-section">
    <div class="glass-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2>Monthly Attendance Summary</h2>
            <div style="display:flex; gap:10px;">
    <a href="export_attendance.php" style="background:#10b981; color:white; text-decoration:none; padding:8px 15px; border-radius:10px; font-weight:700; font-size:0.8rem; display:flex; align-items:center; gap:5px;">
        📊 Download Excel
    </a>
    
    <button onclick="window.print()" style="background:var(--card); color:var(--text); border:1px solid var(--border); padding:8px 15px; border-radius:10px; cursor:pointer; font-weight:700; font-size:0.8rem;">
        🖨️ Print Report
    </button>
</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Days Present</th>
                    <th>Attendance %</th>
                </tr>
            </thead>
            <tbody>

            <?php 
// Updated query to also count 'Late' entries
$report_query = $conn->query("
    SELECT u.full_name, u.student_class,
    COUNT(CASE WHEN a.status = 'Present' THEN 1 END) as days_present,
    COUNT(CASE WHEN a.status = 'Late' THEN 1 END) as days_late,
    COUNT(a.id) as total_days
    FROM users u
    LEFT JOIN attendance a ON u.id = a.student_id
    WHERE u.role = 'student'
    GROUP BY u.id
");

while($rep = $report_query->fetch_assoc()): 
    $percent = ($rep['total_days'] > 0) ? round(($rep['days_present'] / $rep['total_days']) * 100) : 0;
    $color = ($percent >= 75) ? '#10b981' : (($percent >= 50) ? '#f59e0b' : '#f43f5e');
?>
<tr>
    <td>
        <div style="font-weight:600;">
            <?php echo $rep['full_name']; ?>
            <?php if($rep['days_late'] >= 3): ?>
                <span title="Late <?php echo $rep['days_late']; ?> times" style="margin-left:8px; font-size:10px; background:#f59e0b; color:white; padding:2px 6px; border-radius:4px;">⚠ FREQUENT LATE</span>
            <?php endif; ?>
        </div>
    </td>
    <td><span class="badge"><?php echo $rep['student_class']; ?></span></td>
    <td><?php echo $rep['days_present']; ?> / <?php echo $rep['total_days']; ?></td>
    <td>
        <div style="display:flex; align-items:center; gap:10px;">
             <div style="flex:1; height:8px; background:var(--bg); border-radius:10px; overflow:hidden;">
             <div style="width:<?php echo $percent; ?>%; height:100%; background:<?php echo $color; ?>;"></div>
        </div>
        <span style="font-weight:700;"><?php echo $percent; ?>%</span>
    </td>
</tr>
<?php endwhile; ?>

            </tbody>
        </table>
    </div>
</div>
</main>

<div id="addStudentModal" class="modal-overlay">
    <div class="modal-content">
        <h2>Register New Student</h2>
        <form action="add_student_process.php" method="POST">
            <div class="form-group"><label>Full Name</label><input type="text" name="full_name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group">
                <label>Class</label>
                <select name="student_class">
                    <option value="JHS 1">JHS 1</option><option value="JHS 2">JHS 2</option><option value="JHS 3">JHS 3</option>
                    <option value="SHS 1">SHS 1</option><option value="SHS 2">SHS 2</option><option value="SHS 3">SHS 3</option>
                </select>
            </div>
            <input type="hidden" name="password" value="Student123">
            <button type="submit" class="btn-primary">Register Student</button>
            <button type="button" onclick="closeModal()" style="background:none; border:none; color:var(--muted); width:100%; margin-top:10px; cursor:pointer;">Cancel</button>
        </form>
    </div>
</div>

<script>
    function combinedFilter() {
        let search = document.getElementById('studentSearch').value.toLowerCase();
        let cls = document.getElementById('classFilter').value;
        document.querySelectorAll('#studentTable tbody tr').forEach(row => {
            let nameMatch = row.cells[0].textContent.toLowerCase().includes(search);
            let classMatch = (cls === 'all' || row.cells[1].textContent.trim() === cls);
            row.style.display = (nameMatch && classMatch) ? "" : "none";
        });
    }

    function updateTime() { document.getElementById('liveClock').innerText = new Date().toLocaleTimeString(); }
    setInterval(updateTime, 1000); updateTime();

    function toggleTheme() {
        const t = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', t);
        localStorage.setItem('theme', t);
    }
    document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'dark');

    function openModal() { document.getElementById('addStudentModal').style.display = 'flex'; }
    function closeModal() { document.getElementById('addStudentModal').style.display = 'none'; }
    window.onclick = function(e) { if (e.target == document.getElementById('addStudentModal')) closeModal(); }
    
    function filterAttendanceTable() {
    let selectedClass = document.getElementById('attendanceClassFilter').value;
    document.querySelectorAll('.attendance-row').forEach(row => {
        row.style.display = (row.getAttribute('data-class') === selectedClass) ? "" : "none";
    });
}
// Run it once on load to show JHS 1 by default
document.addEventListener('DOMContentLoaded', filterAttendanceTable);

function showView(name) {
    // 1. UI Toggle logic
    document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
    
    document.getElementById('view-' + name).classList.add('active');
    if(document.getElementById('link-' + name)) {
        document.getElementById('link-' + name).classList.add('active');
    }

    // 2. Load the Module
    if(name === 'messages') {
        const container = document.getElementById('messages-ajax-content') || document.getElementById('messages-container');
        
        fetch('load_messages.php')
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
                
                // IMPORTANT: Since we injected new HTML with its own <script>, 
                // we need to tell the browser to execute it.
                const scripts = container.querySelectorAll("script");
                scripts.forEach(oldScript => {
                    const newScript = document.createElement("script");
                    newScript.text = oldScript.text;
                    document.body.appendChild(newScript).parentNode.removeChild(newScript);
                });
            });
    } else {
        // Stop any active chat polling when leaving the messages tab
        if(window.chatTimer) clearInterval(window.chatTimer);
    }
}
</script>
</body>
<?php
// Fetch the latest announcement
$latest_ann = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 1")->fetch_assoc();

// Only show if it was posted in the last 24 hours
$is_recent = false;
if ($latest_ann) {
    $post_date = strtotime($latest_ann['created_at']);
    if (time() - $post_date < 86400) { $is_recent = true; }
}
?>

<?php if ($is_recent): ?>
<div id="announcementPopup" style="position:fixed; bottom:20px; right:20px; width:350px; background:var(--accent); color:white; padding:20px; border-radius:20px; z-index:10000; box-shadow:0 20px 40px rgba(0,0,0,0.4); animation: slideUp 0.5s ease;">
    <div style="display:flex; justify-content:space-between; align-items:start;">
        <h4 style="margin:0; font-size:1.1rem;">📢 <?php echo htmlspecialchars($latest_ann['title']); ?></h4>
        <button onclick="this.parentElement.parentElement.style.display='none'" style="background:none; border:none; color:white; cursor:pointer; font-size:1.2rem;">&times;</button>
    </div>
    <p style="font-size:0.9rem; opacity:0.9; margin:10px 0 0 0;">
        <?php echo htmlspecialchars($latest_ann['message']); ?>
    </p>
</div>

<style>
@keyframes slideUp {
    from { transform: translateY(100px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>
<?php endif; ?>
</html>