<?php
include 'db.php';
session_start();

// Ensure only logged-in teachers can save
if (!isset($_SESSION['user_id'])) { exit("Unauthorized"); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = date('Y-m-d');
    $teacher_id = $_SESSION['user_id']; // This gets YOUR ID
    $student_ids = $_POST['student_ids'];
    $statuses = $_POST['status'];

    // Loop through each student submitted in the form
    for ($i = 0; $i < count($student_ids); $i++) {
        $s_id = mysqli_real_escape_string($conn, $student_ids[$i]);
        $status = mysqli_real_escape_string($conn, $statuses[$i]);

        // YOUR QUERY GOES HERE:
        // It checks if attendance already exists for today; if so, it updates. Otherwise, it inserts.
        $check = $conn->query("SELECT id FROM attendance WHERE student_id = $s_id AND attendance_date = '$date'");
        
        if ($check->num_rows > 0) {
            $conn->query("UPDATE attendance SET status = '$status', teacher_id = $teacher_id WHERE student_id = $s_id AND attendance_date = '$date'");
        } else {
            $conn->query("INSERT INTO attendance (student_id, attendance_date, status, teacher_id) 
                          VALUES ($s_id, '$date', '$status', $teacher_id)");
        }
    }
    
    // Redirect back to dashboard with a success message
    header("Location: dashboard.php?msg=attendance_saved");
}
?>