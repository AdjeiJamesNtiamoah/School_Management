<?php
include 'db.php';
session_start();
$u_id = $_SESSION['user_id']; // Teacher's ID

// Query to get conversations (Same logic as Admin)
$contacts_query = "
    SELECT u.id, u.full_name, u.role, 
           m.message as last_msg, m.attachment as last_file, m.created_at,
           (SELECT COUNT(*) FROM messages WHERE receiver_id = $u_id AND sender_id = u.id AND status = 'unread') as unread_count
    FROM users u
    JOIN messages m ON m.id = (
        SELECT id FROM messages 
        WHERE (sender_id = u.id AND receiver_id = $u_id) OR (sender_id = $u_id AND receiver_id = u.id)
        ORDER BY created_at DESC LIMIT 1
    )
    WHERE u.id != $u_id
    GROUP BY u.id
    ORDER BY m.created_at DESC";

$contacts = $conn->query($contacts_query);
?>

<div class="chat-container">
    <div class="chat-sidebar">
        <div class="sidebar-header">
            <h3>Staff Messages</h3>
            <span class="chat-count"><?= $contacts->num_rows ?></span>
        </div>
        
        <div style="padding: 10px 20px;">
            <input type="text" id="contactSearch" placeholder="Search..." onkeyup="filterContacts()" class="search-input">
        </div>
        
        <div class="contact-list" id="contactList">
            <?php while($row = $contacts->fetch_assoc()): ?>
                <div class="contact-item" onclick="openDirectChat(<?= $row['id'] ?>, '<?= addslashes($row['full_name']) ?>', '<?= $row['role'] ?>')" id="contact-<?= $row['id'] ?>">
                    <div class="avatar"><?= strtoupper(substr($row['full_name'], 0, 1)) ?></div>
                    <div class="contact-info">
                        <div class="contact-top">
                            <span class="contact-name"><?= htmlspecialchars($row['full_name']) ?></span>
                            <small class="contact-time"><?= date('H:i', strtotime($row['created_at'])) ?></small>
                        </div>
                        <div class="contact-bottom">
                            <p class="last-message">
                                <?= !empty($row['last_file']) ? '📎 File' : htmlspecialchars($row['last_msg']) ?>
                            </p>
                            <?php if($row['unread_count'] > 0): ?>
                                <span class="unread-badge"><?= $row['unread_count'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="chatPlaceholder" class="chat-main-area empty">
        <div class="welcome-icon">💬</div>
        <p>Select a user to start chatting</p>
    </div>

    <div id="activeChat" class="chat-main-area" style="display: none;">
        <div class="chat-header">
            <div>
                <div id="activeName" class="header-name">Name</div>
                <div id="activeRole" class="header-role">Role</div>
            </div>
        </div>

        <div id="messageStream" class="message-stream"></div>

        <form id="chatForm" onsubmit="handleChatSubmit(event)" class="chat-input-area">
            <input type="hidden" id="receiver_id">
            <label for="fileInput" class="attach-btn">📎</label>
            <input type="file" id="fileInput" name="attachment" style="display: none;">
            <input type="text" id="msgText" placeholder="Type a message..." autocomplete="off">
            <button type="submit" class="send-btn">Send</button>
        </form>
    </div>
</div>