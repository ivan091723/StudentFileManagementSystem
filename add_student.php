<?php
header('Content-Type: application/json');
require_once 'auth.php';
 
$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Not logged in (session not found). Please log in again."]);
    exit;
}
 
$d = json_decode(file_get_contents("php://input"), true);
if (!is_array($d)) { $d = []; }
 
$id     = trim($d['id'] ?? '');
$name   = trim($d['name'] ?? '');
$course = trim($d['course'] ?? '');
$year   = trim($d['year'] ?? '');
$gpa    = (float)($d['gpa'] ?? 0);
 
if ($id === '' || $name === '') {
    echo json_encode(["success" => false, "error" => "Student ID and name are required."]);
    exit;
}
 
$createdBy  = $user;                                   // the REAL logged-in user (no more 'admin' default)
$status     = is_admin() ? 'approved' : 'pending';     // users need admin approval
$approvedBy = is_admin() ? $user : null;
$approvedAt = is_admin() ? date('Y-m-d H:i:s') : null;
 
try {
    $stmt = $conn->prepare(
        "INSERT INTO students (id, name, course, year, gpa, created_by, status, approved_by, approved_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssdssss", $id, $name, $course, $year, $gpa, $createdBy, $status, $approvedBy, $approvedAt);
 
    if ($stmt->execute()) {
        write_log($conn, is_admin()
            ? "Added student $name ($id)"
            : "Submitted student $name ($id) for admin approval");
 
        echo json_encode([
            "success" => true,
            "status"  => $status,
            "message" => is_admin() ? "Student added." : "Submitted. Waiting for admin approval."
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "Duplicate ID or error"]);
    }
} catch (Throwable $e) {
    echo json_encode(["success" => false, "error" => "Student ID \"$id\" already exists or the data is invalid."]);
}
 