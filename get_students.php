<?php
header('Content-Type: application/json');
require_once 'auth.php';
 
$me = current_user();
if (!$me) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}
 
try {
    if (is_admin()) {
        // Admin directory = approved students only (pending ones live in the Approvals tab)
        $result = $conn->query("SELECT * FROM students WHERE status = 'approved'");
    } else {
        // User sees all approved students + their OWN pending submissions
        $stmt = $conn->prepare(
            "SELECT * FROM students
             WHERE status = 'approved' OR (status = 'pending' AND created_by = ?)"
        );
        $stmt->bind_param("s", $me);
        $stmt->execute();
        $result = $stmt->get_result();
    }
 
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $row['gpa'] = (float)$row['gpa'];
        $students[] = $row;
    }
 
    echo json_encode($students);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([]);
}
 