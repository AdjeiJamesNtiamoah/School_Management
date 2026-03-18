// In your grade posting logic
if(isset($_POST['post_grade'])) {
    $student_id = $_POST['student_id'];
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $score = $_POST['score'];
    
    $conn->query("INSERT INTO grades (student_id, subject_name, marks_obtained) VALUES ($student_id, '$subject', $score)");
    $success = "Grade published to student portal!";
}