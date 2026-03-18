<?php
include 'db.php';
session_start();

// 1. SECURITY: Ensure only finance users can enter
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'finance') {
    header("Location: login.php?error=unauthorized");
    exit();
}

// 2. DEFINE USER VARIABLES (Fixes the $u_name error)
$u_id = $_SESSION['user_id'];

// We fetch the name from the DB to be 100% sure it's accurate
$user_query = $conn->query("SELECT full_name FROM users WHERE id = $u_id");
if ($user_query && $user_query->num_rows > 0) {
    $user_data = $user_query->fetch_assoc();
    $u_name = $user_data['full_name'];
} else {
    $u_name = "Finance Officer"; // Fallback if name isn't found
}

// 3. FETCH FINANCIAL SUMMARY (Calculates total payroll)
// Check if 'salary' column exists to avoid the mysqli_sql_exception
$check_column = $conn->query("SHOW COLUMNS FROM `users` LIKE 'salary'");
if ($check_column->num_rows > 0) {
    $payroll_res = $conn->query("SELECT SUM(salary) as total FROM users WHERE role IN ('staff', 'teacher')");
    $payroll_total = ($payroll_res) ? $payroll_res->fetch_assoc()['total'] : 0;
} else {
    $payroll_total = 0; // Column doesn't exist yet
}

// 4. INBOX COUNT
$msg_res = $conn->query("SELECT COUNT(id) as total FROM messages WHERE receiver_id = $u_id AND status = 'unread'");
$unread_msg = ($msg_res) ? $msg_res->fetch_assoc()['total'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Command Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --bg: #020617; --sidebar: #0f172a; --card: #1e293b; --text: #f8fafc;
            --muted: #94a3b8; --border: rgba(255,255,255,0.08);
            --accent: #00c3ff; --success: #10b981; --danger: #ef4444;
        }

        body {
            background: var(--bg); color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0; display: flex; height: 100vh; overflow: hidden;
        }

        /* --- Sidebar Navigation --- */
        .sidebar {
            width: 260px; background: var(--sidebar);
            padding: 30px 20px; border-right: 1px solid var(--border);
            display: flex; flex-direction: column; flex-shrink: 0;
        }

        .nav-links a {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 18px; color: var(--muted); text-decoration: none;
            font-weight: 600; border-radius: 12px; margin-bottom: 8px;
            transition: 0.3s; cursor: pointer;
        }

        .nav-links a.active, .nav-links a:hover {
            background: rgba(0, 195, 255, 0.1); color: var(--accent);
        }

        /* --- Main Layout --- */
        .viewport { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .header {
            padding: 20px 40px; display: flex; justify-content: space-between;
            align-items: center; border-bottom: 1px solid var(--border);
        }

        .content-area { flex: 1; padding: 40px; overflow-y: auto; }
        .view-section { display: none; animation: fadeIn 0.4s ease; }
        .view-section.active { display: block; }

        /* --- Financial Cards --- */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card {
            background: var(--card); padding: 25px; border-radius: 20px;
            border: 1px solid var(--border); position: relative;
        }
        .stat-card h3 { font-size: 0.8rem; color: var(--muted); text-transform: uppercase; margin: 0 0 10px; }
        .stat-card .amt { font-size: 1.8rem; font-weight: 800; }
        
        /* --- Table Styling --- */
        .data-card { background: var(--card); border-radius: 20px; border: 1px solid var(--border); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: rgba(255,255,255,0.02); text-align: left; padding: 18px; color: var(--muted); font-size: 0.8rem; }
        td { padding: 18px; border-bottom: 1px solid var(--border); font-size: 0.9rem; }

        .badge { padding: 5px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; }
        .badge-paid { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .btn-pay { background: var(--accent); color: #000; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 700; cursor: pointer; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
   
   
    .chat-container { display: flex; height: 100%; background: #0b141a; }
    
    /* Left Sidebar */
    .chat-sidebar { 
        width: 320px; background: #111b21; 
        border-right: 1px solid #222d34; flex-shrink: 0; 
    }
    
    /* Right Chat Area */
    .chat-main { 
        flex: 1; display: flex; flex-direction: column; 
        background-color: #0b141a;
        background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
    }

    /* Message Bubbles with WhatsApp colors */
    .msg-sent { 
        align-self: flex-end; background: #005c4b; color: #e9edef; 
        padding: 8px 12px; border-radius: 8px 0 8px 8px; margin: 4px;
    }
    .msg-received { 
        align-self: flex-start; background: #202c33; color: #e9edef; 
        padding: 8px 12px; border-radius: 0 8px 8px 8px; margin: 4px;
    }


    :root {
        --bg: #020617; --sidebar: #0f172a; --card: #1e293b; --text: #f8fafc;
        --muted: #94a3b8; --border: rgba(255,255,255,0.08);
        --accent: #00c3ff; --success: #10b981;
    }

    body { background: var(--bg); color: var(--text); margin: 0; display: flex; height: 100vh; overflow: hidden; }

    /* Layout Components */
    .sidebar { width: 260px; background: var(--sidebar); border-right: 1px solid var(--border); display: flex; flex-direction: column; }
    .viewport { flex: 1; display: flex; flex-direction: column; }
    .content-area { flex: 1; padding: 30px; overflow-y: auto; }

    /* View Handling */
    .view-section { display: none; height: 100%; }
    .view-section.active { display: block; animation: fadeIn 0.3s ease; }

    /* Messaging UI - WhatsApp Style */
    #view-messages.active { display: flex !important; flex-direction: column; }
    .chat-container { display: flex; flex: 1; background: #0b141a; border-radius: 12px; border: 1px solid var(--border); overflow: hidden; }
    .chat-sidebar { width: 300px; background: #111b21; border-right: 1px solid #222d34; display: flex; flex-direction: column; }
    .chat-main-area { flex: 1; display: flex; flex-direction: column; background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-blend-mode: overlay; }

    .contact-item { padding: 15px; border-bottom: 1px solid #222d34; cursor: pointer; transition: 0.2s; }
    .contact-item:hover { background: #202c33; }
    
    .chat-input-area { padding: 10px 20px; background: #202c33; display: flex; align-items: center; gap: 15px; }
    #msgText { flex: 1; background: #2a3942; border: none; padding: 12px; border-radius: 8px; color: white; outline: none; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }


</style>
   
</head>
<body>

<aside class="sidebar">
    <div style="margin-bottom: 40px;">
        <h2 style="color: var(--accent); font-weight: 800; letter-spacing: -1px;">FINANCE</h2>
    </div>
    
    <nav class="nav-links">
        <a onclick="showView('overview')" id="link-overview" class="active"><i class="fas fa-chart-pie"></i> Overview</a>
        <a onclick="showView('payroll')" id="link-payroll"><i class="fas fa-wallet"></i> Payroll</a>
        <a onclick="showView('ledger')" id="link-ledger"><i class="fas fa-book"></i> Ledger</a>
        <a onclick="showView('messages')" id="link-messages"><i class="fas fa-comment-alt"></i> Inbox (<?= $unread_msg ?>)</a>
        <a href="logout.php" class="nav-link" style="color: var(--danger); margin-top: auto;">🚪 Sign Out</a>
    </nav>

    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--border);">
        <a href="logout.php" style="color: var(--danger);"><i class="fas fa-power-off"></i> Sign Out</a>
    </div>
</aside>

<main class="viewport">
    <header class="header">
        <div>
            <h1 id="view-title" style="margin:0; font-weight: 800; font-size: 1.5rem;">Overview</h1>
            <p style="margin:5px 0 0; color:var(--muted); font-size:0.9rem;">Authenticated as <?= $u_name ?></p>
        </div>
        <div style="display: flex; gap: 15px;">
            <button class="btn-pay" onclick="window.print()"><i class="fas fa-print"></i> Export</button>
        </div>
    </header>

    <div class="content-area">
        
        <div id="view-overview" class="view-section active">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Operational Liquidity</h3>
                    <div class="amt">$542,800.00</div>
                </div>
                <div class="stat-card">
                    <h3>Monthly Payroll</h3>
                    <div class="amt">$<?= number_format($payroll_total, 2) ?></div>
                </div>
                <div class="stat-card">
                    <h3>Expense Ratio</h3>
                    <div class="amt">18.4%</div>
                </div>
            </div>

            <div class="data-card" style="padding: 25px;">
                <h3 style="margin-top:0;">Revenue vs. Disbursements</h3>
                
                <canvas id="mainChart" height="100"></canvas>
            </div>
        </div>

        <div id="view-payroll" class="view-section">
    <div class="data-card">
        <table>
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Salary</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $staff_list = $conn->query("SELECT id, full_name, salary FROM users WHERE role != 'student'");
                while($row = $staff_list->fetch_assoc()):
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td>$<?= number_format($row['salary'], 2) ?></td>
                    <td><span class="badge">Pending</span></td>
                    <td>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="staff_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="amount" value="<?= $row['salary'] ?>">
                            <button type="submit" name="process_payment" class="btn-pay">Release Funds</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

        <div id="view-messages" class="view-section" style="height: calc(100vh - 160px);">
    <div id="chat-ajax-wrapper" style="height: 100%; border-radius: 20px; overflow: hidden; border: 1px solid var(--border);">
        <div style="display:flex; align-items:center; justify-content:center; height:100%; color:var(--muted);">
            <p><i class="fas fa-spinner fa-spin"></i> Initializing Secure Messaging Channel...</p>
        </div>
    </div>
</div>

    </div>
</main>

<script>
let chatTimer;

// --- 1. View Controller ---
function showView(name) {
    // UI Updates
    document.querySelectorAll('.view-section').forEach(v => v.classList.remove('active'));
    document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
    
    const target = document.getElementById('view-' + name);
    const link = document.getElementById('link-' + name);
    
    if(target) target.classList.add('active');
    if(link) link.classList.add('active');
    
    document.getElementById('view-title').innerText = name.charAt(0).toUpperCase() + name.slice(1);

    // Stop polling if we leave messages
    if(name !== 'messages' && chatTimer) clearInterval(chatTimer);

    // Initial load for messages tab
    if(name === 'messages') {
        loadInbox();
    }
}

// --- 2. Messaging Logic ---
function loadInbox() {
    fetch('load_messages.php')
        .then(res => res.text())
        .then(html => {
            document.getElementById('chat-ajax-wrapper').innerHTML = html;
        });
}

function openDirectChat(id, name, role) {
    // UI Toggle
    document.getElementById('chatPlaceholder').style.display = 'none';
    document.getElementById('activeChat').style.display = 'flex';
    
    document.getElementById('activeName').innerText = name;
    document.getElementById('activeRole').innerText = role.toUpperCase();
    document.getElementById('receiver_id').value = id;

    // Start Real-time updates
    fetchMessages(id);
    if(chatTimer) clearInterval(chatTimer);
    chatTimer = setInterval(() => fetchMessages(id), 3000);
}

function fetchMessages(receiverId) {
    fetch(`fetch_messages.php?receiver_id=${receiverId}`)
        .then(res => res.text())
        .then(html => {
            const stream = document.getElementById('messageStream');
            stream.innerHTML = html;
            stream.scrollTop = stream.scrollHeight;
        });
}

async function handleChatSubmit(e) {
    e.preventDefault();
    const msgInput = document.getElementById('msgText');
    const receiver_id = document.getElementById('receiver_id').value;
    
    if(!msgInput.value.trim()) return;

    const formData = new FormData();
    formData.append('receiver_id', receiver_id);
    formData.append('message_body', msgInput.value);

    const res = await fetch('send_message.php', { method: 'POST', body: formData });
    if(res.ok) {
        msgInput.value = '';
        fetchMessages(receiver_id);
    }
}

// --- 3. Dashboard Charts ---
window.onload = () => {
    const ctx = document.getElementById('mainChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Inflow',
                data: [45000, 52000, 48000, 61000, 59000, 65000],
                borderColor: '#00c3ff', tension: 0.4, fill: true,
                backgroundColor: 'rgba(0, 195, 255, 0.05)'
            }, {
                label: 'Outflow',
                data: [38000, 41000, 40000, 45000, 44000, 48000],
                borderColor: '#ef4444', tension: 0.4
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
};
</script>

<script>
    // --- Chart Implementation ---
    const ctx = document.getElementById('financeChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
            datasets: [{
                label: 'Revenue',
                data: [12000, 19000, 15000, 25000, 32000],
                borderColor: '#00c3ff',
                backgroundColor: 'rgba(0, 195, 255, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Expenses',
                data: [10000, 15000, 13000, 18000, 21000],
                borderColor: '#ef4444',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
            }
        }
    });

    // --- View Switcher ---
    function switchView(viewId, element) {
        document.querySelectorAll('.view-section').forEach(v => v.classList.remove('active'));
        document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
        
        document.getElementById('view-' + viewId).classList.add('active');
        element.classList.add('active');
    }

    function generateReport() {
        alert("Preparing Financial Audit Report (PDF)...");
        // Logic for jsPDF generation goes here
    }

    function showView(name) {
        document.querySelectorAll('.view-section').forEach(v => v.classList.remove('active'));
        document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
        
        document.getElementById('view-' + name).classList.add('active');
        document.getElementById('link-' + name).classList.add('active');
        document.getElementById('view-title').innerText = name.charAt(0).toUpperCase() + name.slice(1);

        if(name === 'messages') {
            fetch('load_messages.php').then(r => r.text()).then(h => {
                document.getElementById('msg-loader').innerHTML = h;
            });
        }
    }

    // Chart.js Configuration
    const ctx = document.getElementById('mainChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Inflow',
                data: [45000, 52000, 48000, 61000, 59000, 65000],
                borderColor: '#00c3ff', tension: 0.4, fill: true,
                backgroundColor: 'rgba(0, 195, 255, 0.05)'
            }, {
                label: 'Outflow',
                data: [38000, 41000, 40000, 45000, 44000, 48000],
                borderColor: '#ef4444', tension: 0.4
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
            }
        }
    });

let chatInterval;

function initiateConversation(id, name, role) {
    // Show the chat window
    document.getElementById('chatPlaceholder').style.display = 'none';
    document.getElementById('activeChat').style.display = 'flex';
    document.getElementById('activeName').innerText = name;
    document.getElementById('receiver_id').value = id;

    // Clear any previous refresh loops
    if(chatInterval) clearInterval(chatInterval);

    // Initial load
    refreshStream(id);

    // Set auto-refresh every 3 seconds
    chatInterval = setInterval(() => {
        refreshStream(id);
    }, 3000);
}

function refreshStream(receiver_id) {
    fetch(`fetch_messages.php?receiver_id=${receiver_id}`)
        .then(res => res.text())
        .then(html => {
            const stream = document.getElementById('messageStream');
            stream.innerHTML = html;
            // Only scroll to bottom if user isn't scrolling up to read history
            stream.scrollTop = stream.scrollHeight;
        });
}

function openDirectChat(id, name, role) {
    // 1. Hide the "Select a contact" placeholder
    const placeholder = document.getElementById('chatPlaceholder');
    if(placeholder) placeholder.style.display = 'none';

    // 2. Show the active chat window
    const activeChat = document.getElementById('activeChat');
    if(activeChat) activeChat.style.display = 'flex';

    // 3. Update the header with the student/staff name
    document.getElementById('activeName').innerText = name;
    document.getElementById('activeRole').innerText = role.toUpperCase();
    
    // 4. Store the receiver ID in the hidden input for the form
    document.getElementById('receiver_id').value = id;

    // 5. Load the actual messages
    fetchMessages(id);
}

function fetchMessages(receiverId) {
    fetch(`fetch_messages.php?receiver_id=${receiverId}`)
        .then(response => response.text())
        .then(html => {
            const stream = document.getElementById('messageStream');
            stream.innerHTML = html;
            stream.scrollTop = stream.scrollHeight; // Auto-scroll to bottom
        });
}

// Add this inside finance_dashboard.php script area
let chatTimer; 

function startPolling(id) {
    if(chatTimer) clearInterval(chatTimer);
    chatTimer = setInterval(() => {
        fetchMessages(id);
    }, 3000);
}

// Update your openDirectChat to start polling
function openDirectChat(id, name, role) {
    /* ... previous code ... */
    startPolling(id);
}
</script>

</body>
</html>