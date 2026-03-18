<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { die("Unauthorized"); }
$u_id = $_SESSION['user_id'];

// 1. FETCH ALL CONTACTS (Now shows everyone in the system)
$contacts_query = "
    SELECT u.id, u.full_name, u.role, 
    (SELECT COUNT(*) FROM messages WHERE receiver_id = $u_id AND sender_id = u.id AND status = 'unread') as unread_count,
    (SELECT message_text FROM messages 
     WHERE (sender_id = u.id AND receiver_id = $u_id) OR (sender_id = $u_id AND receiver_id = u.id)
     ORDER BY created_at DESC LIMIT 1) as last_msg,
    (SELECT created_at FROM messages 
     WHERE (sender_id = u.id AND receiver_id = $u_id) OR (sender_id = $u_id AND receiver_id = u.id)
     ORDER BY created_at DESC LIMIT 1) as created_at
    FROM users u
    WHERE u.id != $u_id
    ORDER BY created_at DESC, u.full_name ASC"; // Recent chats first, then alphabetical

$contacts = $conn->query($contacts_query);
?>

<style>
    /* --- WHATSAPP PROFESSIONAL LAYOUT --- */
    .chat-wrapper {
        display: flex;
        width: 100%;
        height: 100%; /* Important: Fills the dashboard's view-section */
        background: #0b141a;
        overflow: hidden;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.05);
    }

    /* --- LEFT SIDEBAR --- */
    .chat-sidebar {
        width: 320px;
        background: #111b21;
        border-right: 1px solid #222d34;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
    }

    .sidebar-header {
        padding: 15px 20px;
        background: #202c33;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #e9edef;
    }

    .contact-list {
        flex: 1;
        overflow-y: auto;
    }

    .contact-item {
        display: flex;
        padding: 12px 15px;
        border-bottom: 1px solid #222d34;
        cursor: pointer;
        transition: 0.2s;
        gap: 12px;
    }

    .contact-item:hover { background: #202c33; }
    .contact-item.active { background: #2a3942; }

    .avatar-circle {
        width: 48px; height: 48px;
        border-radius: 50%;
        background: #6366f1;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        flex-shrink: 0;
    }

    .contact-detail { flex: 1; overflow: hidden; }
    .contact-name-row { display: flex; justify-content: space-between; margin-bottom: 2px; }
    .contact-name { color: #e9edef; font-weight: 500; font-size: 0.95rem; }
    .contact-time { color: #8696a0; font-size: 0.75rem; }
    .contact-msg-row { display: flex; justify-content: space-between; align-items: center; }
    .contact-last-msg { color: #8696a0; font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .unread-pill { background: #00a884; color: #0b141a; font-size: 0.7rem; font-weight: bold; padding: 2px 7px; border-radius: 10px; }

    /* --- RIGHT CHAT AREA --- */
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background-color: #0b141a;
        background-image: linear-gradient(rgba(11, 20, 26, 0.94), rgba(11, 20, 26, 0.94)), 
                         url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
    }

    .chat-top-bar {
        padding: 10px 20px;
        background: #202c33;
        display: flex;
        align-items: center;
        gap: 15px;
        color: #e9edef;
        border-bottom: 1px solid #222d34;
    }

    .message-area {
        flex: 1;
        padding: 20px 30px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    /* --- INPUT AREA --- */
    .chat-footer {
        padding: 10px 15px;
        background: #202c33;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .input-box {
        flex: 1;
        background: #2a3942;
        border: none;
        border-radius: 8px;
        padding: 10px 15px;
        color: #e9edef;
        outline: none;
    }

    .send-btn {
        background: none;
        border: none;
        color: #8696a0;
        font-size: 1.4rem;
        cursor: pointer;
        transition: 0.2s;
    }
    .send-btn:hover { color: #00a884; }

    /* Scrollbar Polish */
    .contact-list::-webkit-scrollbar, .message-area::-webkit-scrollbar { width: 6px; }
    .contact-list::-webkit-scrollbar-thumb, .message-area::-webkit-scrollbar-thumb { background: #374151; border-radius: 10px; }
</style>

<div class="chat-wrapper">
    <div class="chat-sidebar">
        <div class="sidebar-header">
            <h3 style="margin:0; font-size:1.1rem;">Messages</h3>
            <i class="fas fa-edit" style="cursor:pointer; opacity:0.7;"></i>
        </div>
        <div style="padding: 10px 15px; background: #111b21;">
    <div style="background: #202c33; display: flex; align-items: center; padding: 5px 12px; border-radius: 8px;">
        <i class="fas fa-search" style="color: #8696a0; font-size: 0.8rem;"></i>
        <input type="text" id="contactSearch" onkeyup="filterContacts()" 
               placeholder="Search or start new chat" 
               style="background: none; border: none; color: white; padding: 5px 10px; font-size: 0.9rem; outline: none; width: 100%;">
    </div>
</div>
        <div class="contact-list">
            <?php if ($contacts->num_rows > 0): ?>
                <?php while($row = $contacts->fetch_assoc()): ?>
                    <div class="contact-item" onclick="openDirectChat(<?= $row['id'] ?>, '<?= addslashes($row['full_name']) ?>', '<?= $row['role'] ?>')">
                        <div class="avatar-circle">
                            <?= strtoupper(substr($row['full_name'], 0, 1)) ?>
                        </div>
                        <div class="contact-detail">
                            <div class="contact-name-row">
                                <span class="contact-name"><?= htmlspecialchars($row['full_name']) ?></span>
                                <span class="contact-time"><?= date('H:i', strtotime($row['created_at'])) ?></span>
                            </div>
                            <div class="contact-msg-row">
                                <span class="contact-last-msg">
                                    <?php 
                                    if (!empty($row['last_msg'])) {
                                    echo htmlspecialchars($row['last_msg']);
                                    } else {
                                    echo "<i style='color:var(--accent); opacity:0.7;'>Click to start a conversation</i>";
                                    }
                                    ?>
                                </span>
                            <?php if($row['unread_count'] > 0): ?>
                                <span class="unread-pill"><?= $row['unread_count'] ?></span>
                            <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; color:#8696a0; padding:20px;">No messages yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="chatPlaceholder" class="chat-main" style="justify-content: center; align-items: center; text-align: center;">
        <div style="font-size: 4rem; opacity: 0.1; color: white; margin-bottom: 15px;"><i class="fas fa-comments"></i></div>
        <h2 style="color: #e9edef; font-weight: 300;">WhatsApp for Dashboard</h2>
        <p style="color: #8696a0; max-width: 280px; font-size: 0.9rem;">Select a contact to view your conversation or start a new chat.</p>
    </div>

    <div id="activeChat" class="chat-main" style="display: none;">
        <div class="chat-top-bar">
            <div id="activeAvatar" class="avatar-circle" style="width:40px; height:40px; font-size:0.9rem;"></div>
            <div>
                <div id="activeName" style="font-weight: 600; font-size: 1rem;">User Name</div>
                <div id="activeRole" style="font-size: 0.75rem; color: #00a884; font-weight: bold;">STAFF</div>
            </div>
        </div>

        <div id="messageStream" class="message-area">
            </div>

        <form id="chatInputForm" onsubmit="handleChatSubmit(event)" class="chat-footer">
            <input type="hidden" id="receiver_id">
            <label for="fileInput" style="cursor: pointer; color: #8696a0; font-size: 1.2rem;">
            <i class="fas fa-paperclip"></i>
        </label>
        <input type="file" id="fileInput" name="attachment" style="display: none;">
            <input type="text" id="msgText" class="input-box" placeholder="Type a message" autocomplete="off">
            <button type="submit" class="send-btn"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
</div>



<script>
// Use window object to ensure these functions are globally accessible 
// even when loaded via AJAX.

window.openDirectChat = function(id, name, role) {
    const placeholder = document.getElementById('chatPlaceholder');
    const activeChat = document.getElementById('activeChat');
    
    if(placeholder) placeholder.style.display = 'none';
    if(activeChat) activeChat.style.display = 'flex';
    
    document.getElementById('activeName').innerText = name;
    document.getElementById('activeRole').innerText = role.toUpperCase();
    document.getElementById('receiver_id').value = id;

    // Fetch immediately
    window.fetchMessages(id, true);

    // Set up Polling
    if(window.chatTimer) clearInterval(window.chatTimer);
    window.chatTimer = setInterval(() => window.fetchMessages(id), 3000);
    
    // UI highlight
    document.querySelectorAll('.contact-item').forEach(i => i.classList.remove('active'));
    event.currentTarget.classList.add('active');
};

window.fetchMessages = function(receiverId, isInitial = false) {
    const stream = document.getElementById('messageStream');
    if(!stream) return;

    fetch(`fetch_messages.php?receiver_id=${receiverId}`)
        .then(res => res.text())
        .then(html => {
            stream.innerHTML = html;
            
            // Auto-scroll logic
            if(isInitial) {
                stream.scrollTop = stream.scrollHeight;
            } else {
                // Only scroll if user is near bottom
                const isNearBottom = stream.scrollHeight - stream.scrollTop <= stream.clientHeight + 100;
                if(isNearBottom) stream.scrollTop = stream.scrollHeight;
            }
        });
};

window.handleChatSubmit = async function(e) {
    e.preventDefault();
    const input = document.getElementById('msgText');
    const rid = document.getElementById('receiver_id').value;
    const msg = input.value.trim();

    if(!msg || !rid) return;

    const fd = new FormData();
    fd.append('receiver_id', rid);
    fd.append('message_body', msg);

    input.value = ''; // Clear UI immediately

    const res = await fetch('send_message.php', { method: 'POST', body: fd });
    if(res.ok) {
        window.fetchMessages(rid);
    }
};

function filterContacts() {
    let input = document.getElementById('contactSearch').value.toLowerCase();
    let contacts = document.querySelectorAll('.contact-item');
    
    contacts.forEach(item => {
        let name = item.querySelector('.contact-name').innerText.toLowerCase();
        item.style.display = name.includes(input) ? "flex" : "none";
    });
}
</script>
