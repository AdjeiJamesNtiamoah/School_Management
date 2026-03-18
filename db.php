<?php
$host = "localhost";
$user = "root"; // Default for XAMPP/WAMP
$pass = "";     // Default is empty
$dbname = "School_Management"; // The name you gave in phpMyAdmin
$conn = new mysqli($host, $user, $pass, $dbname);

// Add this to your main include file
$m_check = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode'")->fetch_assoc();

if ($m_check['setting_value'] == 1) {
    $current = basename($_SERVER['PHP_SELF']);
    // ALLOW admin_dashboard, toggle_maintenance, and logout to work
    $allowed_pages = ['admin_dashboard.php', 'toggle_maintenance.php', 'logout.php', 'maintenance.php', 'login.php'];
    
    if (!in_array($current, $allowed_pages)) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: maintenance.php");
            exit();
        }
    }
}
?>