<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // NEW: Capture the class from the dropdown
    $student_class = mysqli_real_escape_string($conn, $_POST['student_class']);
    
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'student';

    // Verify email doesn't exist
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    
    if ($check->num_rows > 0) {
        header("Location: dashboard.php?msg=email_exists");
    } else {
        // UPDATE: Include 'student_class' in the INSERT statement
        $sql = "INSERT INTO users (full_name, email, password, role, student_class) 
                VALUES ('$full_name', '$email', '$password', '$role', '$student_class')";
        
        if ($conn->query($sql)) {
            header("Location: dashboard.php?msg=student_added");
        } else {
            header("Location: dashboard.php?msg=error");
        }
    }
}
exit();
?>