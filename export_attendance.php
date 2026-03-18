<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) { exit("Unauthorized"); }

// 1. Set Headers to force download as Excel/CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Attendance_Report_'.date('Y-m-d').'.csv');

// 2. Open "output" stream
$output = fopen('php://output', 'w');

// 3. Write Column Headers
fputcsv($output, array('Student Name', 'Class', 'Days Present', 'Days Late', 'Total Days', 'Attendance %'));

// 4. Fetch Data
$query = "SELECT u.full_name, u.student_class,
          COUNT(CASE WHEN a.status = 'Present' THEN 1 END) as days_present,
          COUNT(CASE WHEN a.status = 'Late' THEN 1 END) as days_late,
          COUNT(a.id) as total_days
          FROM users u
          LEFT JOIN attendance a ON u.id = a.student_id
          WHERE u.role = 'student'
          GROUP BY u.id";

$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $percent = ($row['total_days'] > 0) ? round(($row['days_present'] / $row['total_days']) * 100) : 0;
    
    // Write the row to the CSV
    fputcsv($output, array(
        $row['full_name'],
        $row['student_class'],
        $row['days_present'],
        $row['days_late'],
        $row['total_days'],
        $percent . '%'
    ));
}

fclose($output);
exit();
?>