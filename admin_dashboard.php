<?php
include 'db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


// Put this at the very top of manage_users.php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied: You do not have administrative privileges.");
}
$u_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Administrator';

// GLOBAL COUNTS for the Sidebar Badges
$m_count_query = $conn->query("SELECT COUNT(*) as c FROM messages WHERE status = 'unread' AND receiver_id = $u_id");
$m_count = $m_count_query->fetch_assoc()['c'];

// Optional: Count pending student registrations if you have that feature
$p_count_query = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'student' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$new_users = $p_count_query->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #020617; --sidebar: #0f172a; --panel: #1e293b;
            --accent: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
            --text: #f8fafc; --muted: #94a3b8; --border: rgba(255,255,255,0.06);
            --danger: #f43f5e; --success: #10b981;
        }

        body { 
            background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; display: flex; height: 100vh; overflow: hidden; 
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 280px; background: var(--sidebar); border-right: 1px solid var(--border);
            padding: 2.5rem 1.5rem; display: flex; flex-direction: column;
        }

        .nav-link {
            display: flex; align-items: center; padding: 14px 18px; color: var(--muted);
            text-decoration: none; border-radius: 14px; margin-bottom: 8px;
            font-weight: 600; cursor: pointer; transition: all 0.3s ease;
        }

        .nav-link:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .nav-link.active { 
            background: var(--accent); color: white; 
            box-shadow: 0 10px 20px -5px rgba(168, 85, 247, 0.4); 
        }

        /* Dynamic Workspace */
        .workspace-container {
            flex: 1; display: flex; flex-direction: column; overflow: hidden;
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.03), transparent);
        }

        .top-bar {
            padding: 1.5rem 3rem; border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }

        #main-workspace {
            flex: 1; padding: 2.5rem 3rem; overflow-y: auto;
        }

        /* Professional Stat Cards */
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: var(--panel); border: 1px solid var(--border); padding: 1.5rem; border-radius: 24px; }
        .stat-card h2 { font-size: 2.2rem; margin: 8px 0 0; font-weight: 800; }

        /* Loading Spinner */
        .loader { width: 30px; height: 30px; border: 3px solid var(--muted); border-top-color: #a855f7; border-radius: 50%; animation: spin 1s linear infinite; display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Additional Workspace Styles */
.btn-action { 
    background: var(--accent); color: white; border: none; padding: 12px 24px; 
    border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s;
}

.glass-card { 
    background: var(--panel); border: 1px solid var(--border); 
    border-radius: 24px; padding: 2rem; 
    box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
}

/* Custom Scrollbar for Workspace */
#main-workspace::-webkit-scrollbar { width: 6px; }
#main-workspace::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

.badge-count {
    background: var(--danger);
    color: white;
    font-size: 0.65rem;
    padding: 2px 6px;
    border-radius: 6px;
    margin-left: auto;
    font-weight: 800;
    box-shadow: 0 0 10px rgba(244, 63, 94, 0.4);
}

#toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}
.toast {
    background: var(--panel);
    border-left: 4px solid var(--accent);
    color: white;
    padding: 15px 25px;
    border-radius: 8px;
    margin-top: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideIn 0.3s ease-out;
}
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Smooth Scroll for Chat */
#messageStream {
    scroll-behavior: smooth;
}

/* Hide scrollbar for a cleaner look but keep functionality */
#messageStream::-webkit-scrollbar {
    width: 4px;
}
#messageStream::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
}

.modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.7); backdrop-filter: blur(5px);
        display: none; align-items: center; justify-content: center; z-index: 1000;
    }
    .modal-content { width: 500px; padding: 2rem; border-color: var(--accent); animation: modalSlide 0.3s ease; }
    @keyframes modalSlide { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .close-btn { background: none; border: none; color: var(--muted); font-size: 1.5rem; cursor: pointer; }
    .close-btn:hover { color: var(--danger); }

.contact-item.active {
    background: #1c1e26;
    border-left: 4px solid #8b5cf6; /* Your purple accent color */
}

/* Professional Message Bubbles */
.msg-wrapper { display: flex; margin-bottom: 12px; width: 100%; }
.my-msg { justify-content: flex-end; }
.their-msg { justify-content: flex-start; }

.msg-bubble { 
    max-width: 70%; padding: 12px 16px; border-radius: 18px; 
    font-size: 0.9rem; line-height: 1.5; position: relative;
}

.my-msg .msg-bubble { 
    background: var(--accent); color: white; border-bottom-right-radius: 4px; 
}

.their-msg .msg-bubble { 
    background: #1e293b; color: #f1f5f9; border-bottom-left-radius: 4px; 
    border: 1px solid var(--border);
}

.msg-meta { 
    display: flex; align-items: center; justify-content: flex-end; 
    gap: 5px; font-size: 0.7rem; margin-top: 4px; opacity: 0.7; 
}

.status-tick { color: #38bdf8; font-weight: bold; }
.empty-chat { text-align: center; color: var(--muted); margin-top: 50px; font-style: italic; }

/* Chat Background Pattern */
.message-stream {
    background-color: #0b141a; /* WhatsApp Dark Mode Base */
    background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
    background-blend-mode: overlay;
    background-size: 400px;
    padding: 20px 7%;
    display: flex;
    flex-direction: column;
}

/* Row Alignment */
.message-row { display: flex; width: 100%; margin-bottom: 4px; }
.row-right { justify-content: flex-end; }
.row-left { justify-content: flex-start; }

/* The Bubbles */
.bubble {
    max-width: 65%;
    padding: 8px 12px 6px 12px;
    border-radius: 8px;
    position: relative;
    box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
    font-size: 0.92rem;
}

.bubble-sent {
    background: #005c4b; /* WhatsApp Green */
    color: #e9edef;
    border-top-right-radius: 0;
}

.bubble-received {
    background: #202c33; /* WhatsApp Grey */
    color: #e9edef;
    border-top-left-radius: 0;
}

/* Metadata (Time & Ticks) */
.bubble-meta {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    font-size: 0.65rem;
    margin-top: 2px;
    opacity: 0.6;
}

.ticks-read { color: #53bdeb; } /* Blue Ticks */
.ticks-sent { color: #8696a0; } /* Grey Ticks */

/* Voice Note Placeholder Styling */
.voice-content { display: flex; align-items: center; gap: 10px; padding: 5px 0; }
.voice-wave { height: 2px; width: 100px; background: rgba(255,255,255,0.2); border-radius: 2px; }

.chat-empty {
    align-self: center;
    background: #182229;
    padding: 6px 12px;
    border-radius: 8px;
    color: #8696a0;
    font-size: 0.75rem;
    margin-top: 20px;
}

.avatar-container { position: relative; width: 40px; height: 40px; }

.status-dot {
    position: absolute; bottom: 2px; right: 2px;
    width: 12px; height: 12px;
    border-radius: 50%;
    border: 2px solid #0c0d11; /* Matches chat background */
}

.status-dot.online { 
    background: #22c55e; /* WhatsApp Green */
    box-shadow: 0 0 8px rgba(34, 197, 94, 0.6); 
}

.status-dot.offline { background: #64748b; }

.user-meta small {
    font-size: 0.75rem;
    color: #8696a0; /* Muted grey */
}
    </style>
</head>
<body>

<aside class="sidebar">
    <div style="font-size: 1.8rem; font-weight: 800; background: var(--accent); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 3rem;">FLAWLESS</div>
    
    <nav id="sidebar-nav">
    <a class="nav-link active" data-page="load_overview.php">
        🏠 Overview
    </a>
    <a class="nav-link" data-page="load_users.php">
        👥 Manage Users
        <?php if($new_users > 0): ?>
            <span class="badge-count" style="background: var(--success)20; color: var(--success);">+<?= $new_users ?></span>
        <?php endif; ?>
    </a>
    <a class="nav-link" data-page="load_messages.php">
        💬 Support Inbox
        <?php if($m_count > 0): ?>
            <span id="global-unread-count" class="badge-count"><?= $m_count ?></span>
        <?php endif; ?>
    </a>
    <a class="nav-link" data-page="load_settings.php">⚙️ System Settings</a>
    
    <a href="logout.php" class="nav-link" style="color: var(--danger); margin-top: auto;">🚪 Sign Out</a>
    
    <a class="nav-link" data-page="load_profile.php" style="border-top: 1px solid var(--border); padding-top: 20px; margin-top: 10px;">
    👤 Profile Settings
</a>
</a>

</nav>
</aside>

<section class="workspace-container">
    <header class="top-bar">
        <div>
            <h2 id="page-title" style="margin:0; font-weight: 800; letter-spacing: -1px;">Dashboard</h2>
        </div>
        <div style="display:flex; align-items:center; gap:1.2rem;">
            <div id="loader" class="loader"></div>
            <div style="text-align:right">
                <div style="font-weight:700; font-size:0.9rem;"><?= htmlspecialchars($full_name) ?></div>
                <div style="font-size:0.75rem; color:var(--success);">● Active Session</div>
            </div>
            <div style="width:42px; height:42px; background: var(--accent); border-radius: 12px;"></div>
        </div>
    </header>

    <div id="main-workspace">
        </div>
</section>


 <section id="view-messages" class="view-section">
            <div id="messages-ajax-content" style="height:100%; width:100%;"></div>
        </section>

<script>
    const workspace = document.getElementById('main-workspace');
    const loader = document.getElementById('loader');
    const pageTitle = document.getElementById('page-title');
    const navLinks = document.querySelectorAll('.nav-link');

    // 1. Unified Navigation and Default Load
document.addEventListener('DOMContentLoaded', () => {
    // Correct logic for clicking sidebar links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.getAttribute('data-page');
            const title = this.innerText.replace(/[^\w\s]/gi, '').trim(); 
            if (page) loadPage(page, title, this);
        });
    });

    // Default load the overview page
    loadPage('load_overview.php', 'Dashboard', document.querySelector('.nav-link.active'));
    requestNotificationPermission();
});

// 2. The Core Page Loading Function
function loadPage(page, title, element) {
    const workspace = document.getElementById('main-workspace');
    const loader = document.getElementById('loader');
    const pageTitle = document.getElementById('page-title');

    if(loader) loader.style.display = 'block';
    if(pageTitle) pageTitle.innerText = title;
    
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    if(element) element.classList.add('active');

    fetch(page)
        .then(response => response.text())
        .then(data => {
            workspace.innerHTML = data;
            if(loader) loader.style.display = 'none';
        })
        .catch(err => {
            console.error("Load error:", err);
            workspace.innerHTML = '<p style="color:white; padding:20px;">Error loading content.</p>';
            if(loader) loader.style.display = 'none';
        });
}

// 3. Consolidated Chat Logic (Fixing the Duplicate & Crash)
// Use window.chatInterval to ensure it's globally accessible and not re-declared
window.chatInterval = window.chatInterval || null;

function openDirectChat(id, name, role) {
    // 1. UI Updates
    document.getElementById('chatPlaceholder').style.display = 'none';
    document.getElementById('activeChat').style.display = 'flex';
    document.getElementById('receiver_id').value = id;
    document.getElementById('activeName').innerText = name;
    document.getElementById('activeRole').innerText = role;

    // 2. Mark messages as read in the database
    const formData = new FormData();
    formData.append('sender_id', id);

    fetch('mark_read.php', {
        method: 'POST',
        body: formData
    })
    .then(() => {
        // 3. Immediately hide the red badge in the sidebar for this contact
        const contactItem = document.getElementById(`contact-${id}`);
        if (contactItem) {
            const badge = contactItem.querySelector('.unread-badge');
            if (badge) badge.style.display = 'none';
        }
    });

    // 4. Start polling for messages
    if (chatInterval) clearInterval(chatInterval);
    loadMessages(id);
    chatInterval = setInterval(() => loadMessages(id), 3000);
}
function loadMessages(receiverId) {
    fetch(`fetch_messages.php?receiver_id=${receiverId}`)
        .then(res => res.text())
        .then(html => {
            const stream = document.getElementById('messageStream');
            // Only update if content changed to prevent scrolling issues
            if (stream.innerHTML !== html) {
                stream.innerHTML = html;
                stream.scrollTop = stream.scrollHeight;
            }
        });
}


// 4. Fixed File Upload Function (Removed the Orphaned .then block)
function uploadChatFile() {
    const fileInput = document.getElementById('fileInput');
    const rid = document.getElementById('receiver_id').value;
    
    if (!fileInput.files[0] || !rid) return;

    const formData = new FormData();
    formData.append('attachment', fileInput.files[0]);
    formData.append('receiver_id', rid);
    formData.append('message', 'Sent a file'); 

    fetch('send_message.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            fileInput.value = ''; 
            loadMessages(rid);
            showToast("File sent successfully!", "success");
        } else {
            showToast("Upload failed: " + data.message, "danger");
        }
    })
    .catch(err => console.error("Upload error:", err));
}

// 5. Shared UI Utilities
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container') || (() => {
        const c = document.createElement('div');
        c.id = 'toast-container';
        document.body.appendChild(c);
        return c;
    })();
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.style.borderLeftColor = type === 'success' ? 'var(--success)' : 'var(--danger)';
    toast.innerHTML = `<span>${type === 'success' ? '✅' : '❌'}</span> ${message}`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

async function handleChatSubmit(e) {
    e.preventDefault();
    
    // 1. Get references to the elements
    const msgInput = document.getElementById('msgText');
    const receiverInput = document.getElementById('receiver_id');
    const fileInput = document.getElementById('fileInput'); // The problematic element

    // 2. Safety Check: If these don't exist, exit quietly
    if (!msgInput || !receiverInput) return;

    const messageBody = msgInput.value.trim();
    const receiverId = receiverInput.value;

    // 3. Prepare FormData
    const formData = new FormData();
    formData.append('receiver_id', receiverId);
    formData.append('message_body', messageBody);

    // 4. NULL-SAFE File Check: Only append if fileInput exists AND has a file
    if (fileInput && fileInput.files && fileInput.files.length > 0) {
        formData.append('attachment', fileInput.files[0]);
    }

    if (!messageBody && (!fileInput || fileInput.files.length === 0)) return;

    // Clear UI immediately for responsiveness
    msgInput.value = '';
    if (fileInput) fileInput.value = ''; 

    try {
        const response = await fetch('send_message.php', {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            // Re-fetch messages to show the one just sent
            if (window.fetchMessages) window.fetchMessages(receiverId);
        } else {
            console.error("Server returned an error.");
        }
    } catch (error) {
        console.error("Fetch error:", error);
    }
}

/** * User Presence Heartbeat
 */
function updatePresence() {
    fetch('update_status.php');
}

// Update status every 60 seconds and on initial load
setInterval(updatePresence, 60000);
document.addEventListener('DOMContentLoaded', updatePresence);

/**
 * Global User Deletion Function
 */
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
        // This ensures $ is defined before the code inside runs
$(document).ready(function() {
    
    // Move your notification or initialization logic here
    if (typeof requestNotificationPermission === "function") {
        requestNotificationPermission();
    }

    console.log("jQuery is active and the dashboard is ready.");
});
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
/**
 * Global Edit Function
 */
window.editUser = function(id) {
    // Redirects to the edit page with the specific user ID
    window.location.href = `edit_user.php?id=${id}`;
};

/**
 * Request permission for Desktop Notifications
 */
// A. DEFINE FUNCTIONS FIRST (Plain JavaScript)
function requestNotificationPermission() {
    if ("Notification" in window) {
        Notification.requestPermission();
    }
}

// B. USE JQUERY AFTER (Inside the document ready block)
$(document).ready(function() {
    // Now $ is defined because jQuery loaded in the <head>
    requestNotificationPermission();
    
    // Any other jQuery logic goes here
    console.log("Dashboard Ready!");
});

// C. GLOBAL AJAX FUNCTIONS (For your load_users.php buttons)
window.confirmDelete = function(id, name) {
    if (!confirm(`Are you sure you want to remove ${name}?`)) return;

    const formData = new FormData();
    formData.append('id', id);

    fetch('delete_user.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // This looks for the id="user-row-X" we added in Step A
            const row = document.getElementById(`user-row-${id}`);
            if (row) {
                row.style.transition = "0.3s";
                row.style.opacity = '0';
                row.style.transform = "translateX(20px)";
                setTimeout(() => row.remove(), 300);
            }
            showToast("User removed successfully", "success");
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => {
        console.error("Delete Error:", err);
        alert("Server error. Please check if delete_user.php exists.");
    });
};

// 1. Wrap the File Input listener
const fileInput = document.getElementById('fileInput');
if (fileInput) {
    fileInput.addEventListener('change', function() {
        console.log("File selected");
        // Your preview logic here
    });
}

// 2. Wrap the Form listener (if you have one)
const chatForm = document.getElementById('chatForm');
if (chatForm) {
    chatForm.addEventListener('submit', function(e) {
        window.handleChatSubmit(e);
    });
}

window.startConversation = function(id, name, role) {
    // 1. Find the Support Inbox link in the sidebar
    const inboxLink = document.querySelector('[data-page="load_messages.php"]');
    
    // 2. Load the messages page first
    loadPage('load_messages.php', 'Support Inbox', inboxLink);

    // 3. Wait for the page to finish loading, then open the specific chat
    // We use a small timeout to ensure the HTML elements are ready
    setTimeout(() => {
        if (typeof openDirectChat === "function") {
            openDirectChat(id, name, role);
        } else {
            console.error("openDirectChat function not found. Make sure load_messages.php is ready.");
        }
    }, 500); 
};

let typingTimer;
const msgInput = document.getElementById('msgInput');

msgInput.addEventListener('input', () => {
    // Tell the server I am typing to activeId
    updateTypingStatus(activeId);
    
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
        updateTypingStatus(0); // Stop typing after 2 seconds of inactivity
    }, 2000);
});

function updateTypingStatus(toId) {
    fetch('update_status.php', {
        method: 'POST',
        body: new URLSearchParams({ 'typing_to': toId })
    });
}

function checkTyping() {
    fetch(`check_typing.php?user_id=${activeId}`)
        .then(res => res.json())
        .then(data => {
            const statusDiv = document.getElementById('typing-indicator');
            if(data.is_typing) {
                statusDiv.innerText = "typing...";
                statusDiv.style.color = "#10b981";
            } else {
                statusDiv.innerText = "";
            }
        });
}
// Run this inside your chat interval
setInterval(checkTyping, 2000);

function loadAdminSidebar() {
    fetch('admin_load_contacts.php')
        .then(res => res.text())
        .then(html => {
            const sidebar = document.getElementById('adminContactList');
            if(sidebar) {
                // Using innerHTML replaces the old list, fixing the "double name" issue
                sidebar.innerHTML = html;
            }
        });
}

// Open chat when a name is clicked
function openAdminChat(userId, userName) {
    activeId = userId;
    document.getElementById('adminChatHeader').style.display = 'block';
    document.getElementById('adminMsgForm').style.display = 'flex';
    document.getElementById('activeContactName').innerText = userName;
    
    // Highlight the selected contact in sidebar
    document.querySelectorAll('.contact-item').forEach(el => el.classList.remove('active'));
    // (Optional: add logic to find the specific element and add .active class)
    
    fetchMessages(); // Call your existing fetch function
}

// Initial load
document.addEventListener('DOMContentLoaded', loadAdminSidebar);

/**
 * Global View Switcher with AJAX Module Loading
 * Handles dynamic script execution to prevent "Null" property errors.
 */
function showView(name) {
    // 1. UI RESET: Remove active classes from all sections and links
    document.querySelectorAll('.view-section').forEach(section => {
        section.classList.remove('active');
    });
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.classList.remove('active');
    });

    // 2. ACTIVATE: Show the target section and highlight the sidebar link
    const targetSection = document.getElementById('view-' + name);
    const targetLink = document.getElementById('link-' + name);
    const pageTitle = document.getElementById('page-title');

    if (targetSection) targetSection.classList.add('active');
    if (targetLink) targetLink.classList.add('active');
    if (pageTitle) pageTitle.innerText = name.charAt(0).toUpperCase() + name.slice(1);

    // 3. MODULE LOADING: Special logic for the Messages tab
    if (name === 'messages') {
        const container = document.getElementById('messages-ajax-content');
        
        // Show a loading state while fetching
        container.innerHTML = `
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:var(--muted);">
                <i class="fas fa-circle-notch fa-spin" style="font-size:2rem; margin-bottom:10px;"></i>
                <p>Loading Encrypted Chat...</p>
            </div>`;

        fetch('load_messages.php')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                // Inject the HTML into the dashboard
                container.innerHTML = html;

                /**
                 * CRITICAL FIX FOR "NULL" ERRORS:
                 * We must manually extract and re-execute scripts found in the injected HTML.
                 * This ensures addEventListener() finds the elements AFTER they exist.
                 */
                const injectedScripts = container.querySelectorAll("script");
                injectedScripts.forEach(oldScript => {
                    const newScript = document.createElement("script");
                    
                    // Copy all attributes (like src) and the actual code content
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                    
                    // Append to body to execute, then immediately remove to keep DOM clean
                    document.body.appendChild(newScript);
                    document.body.removeChild(newScript);
                });
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                container.innerHTML = '<div style="padding:20px; color:#f43f5e;">Failed to load messages. Please refresh.</div>';
            });
    } else {
        // 4. MEMORY MANAGEMENT: Stop chat polling if the user leaves the messages view
        if (window.chatTimer) {
            console.log("Chat polling stopped.");
            clearInterval(window.chatTimer);
            window.chatTimer = null;
        }
    }
}
</script>

<div id="globalModal" class="modal-overlay">
    <div class="modal-content glass-card">
        <div class="modal-header">
            <h3 id="modalTitle">Add New System Member</h3>
            <button onclick="closeModal()" class="close-btn">&times;</button>
        </div>
        <div id="modalBody">
            </div>
    </div>
</div>

<div id="callModal" class="call-overlay" style="display:none;">
    <div class="call-card">
        <video id="remoteVideo" autoplay playsinline></video>
        <video id="localVideo" autoplay playsinline muted></video>
        
        <div class="call-ui">
            <h4 id="callStatus">Calling...</h4>
            <div class="call-btns">
                <button onclick="endCall()" class="end-call-btn">✖</button>
                <button onclick="toggleMic()" id="muteBtn" class="call-icon-btn">🎤</button>
            </div>
        </div>
    </div>
</div>


<style>
    .call-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; display: flex; align-items: center; justify-content: center; }
    .call-card { position: relative; width: 80%; max-width: 900px; aspect-ratio: 16/9; background: #111; border-radius: 20px; overflow: hidden; }
    #remoteVideo { width: 100%; height: 100%; object-fit: cover; }
    #localVideo { position: absolute; bottom: 20px; right: 20px; width: 200px; border-radius: 10px; border: 2px solid #8b5cf6; }
    .call-ui { position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); text-align: center; }
    .end-call-btn { background: #ef4444; color: white; border: none; padding: 15px 30px; border-radius: 50px; cursor: pointer; font-weight: bold; }
</style>

<div id="incomingCallModal" class="incoming-overlay" style="display:none;">
    <div class="incoming-card">
        <div class="pulse-avatar">📞</div>
        <h3 id="callerName">Incoming Call...</h3>
        <p id="callTypeLabel">Video Call</p>
        <div class="incoming-btns">
            <button onclick="respondToCall('accepted')" class="accept-btn">Accept</button>
            <button onclick="respondToCall('declined')" class="decline-btn">Decline</button>
        </div>
    </div>
</div>

<style>
.incoming-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
.incoming-card { background: #1c1e26; padding: 40px; border-radius: 24px; text-align: center; border: 1px solid #2d2f39; width: 300px; }
.pulse-avatar { font-size: 40px; background: #8b5cf6; width: 80px; height: 80px; line-height: 80px; border-radius: 50%; margin: 0 auto 20px; animation: pulseCall 1.5s infinite; }
.incoming-btns { display: flex; gap: 15px; margin-top: 30px; }
.accept-btn { flex: 1; background: #22c55e; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: bold; }
.decline-btn { flex: 1; background: #ef4444; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: bold; }
@keyframes pulseCall { 0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.7); } 70% { box-shadow: 0 0 0 20px rgba(139, 92, 246, 0); } 100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); } }

.admin-messaging-wrapper {
    display: flex;
    height: calc(100vh - 160px); /* Adjust based on your header height */
    background: #0f172a;
    border-radius: 15px;
    border: 1px solid #1e293b;
    overflow: hidden;
}

.admin-chat-sidebar {
    width: 280px; /* Fixed width for the Support Inbox */
    border-right: 1px solid #1e293b;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #1e293b;
}

.scrollable-contacts {
    flex: 1;
    overflow-y: auto; /* Scroll only inside the sidebar */
}

/* Individual Contact Item */
.contact-card {
    padding: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    cursor: pointer;
    transition: 0.2s;
}

.contact-card:hover {
    background: rgba(139, 92, 246, 0.1);
}

.admin-chat-window {
    flex: 1; /* Takes up the rest of the space */
    display: flex;
    flex-direction: column;
    background: #0b141a;
}



.chat-input-area {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    background: #202c33; /* WhatsApp Dark Input Background */
    gap: 12px;
}

#msgText {
    flex: 1; /* This makes the input fill the middle space */
    background: #2a3942;
    border: none;
    border-radius: 8px;
    padding: 9px 12px;
    color: #d1d7db;
    font-size: 15px;
    outline: none;
}

.send-btn {
    background: transparent;
    border: none;
    color: #8696a0;
    cursor: pointer;
    font-size: 1.5rem;
    padding: 5px;
}
</style>

</body>
</html>