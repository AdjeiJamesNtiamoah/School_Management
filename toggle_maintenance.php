<?php
include 'db.php';
session_start();

if ($_SESSION['role'] === 'admin') {
    $conn->query("UPDATE system_settings SET setting_value = 1 - setting_value WHERE setting_key = 'maintenance_mode'");
}
header("Location: admin_dashboard.php");
?>