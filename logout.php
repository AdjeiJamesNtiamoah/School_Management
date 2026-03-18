<?php
session_start();
session_destroy(); // Clears all login data
header("Location: login.php");
exit();
?>