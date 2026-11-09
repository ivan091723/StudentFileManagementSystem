<?php
include "db.php";
require_login();
$d = json_decode(file_get_contents("php://input"), true);
$u = $_SESSION['username'];
 
// Users can only edit the students they added. Admin can edit anyone.
if ($_SESSION['role'] !== 'admin') {
    $chk = $conn->prepare("SELECT id FROM students WHERE id=? AND created_by=?");
    $chk->bind_param("ss", $d['oldId'], $u);
    $chk->execute();
    if ($chk->get_result()->num_rows === 0) {
        fail("You can only edit your own records.", 403);
    }
}
 
$stmt = $conn->prepare("UPDATE students SET id=?, name=?, course=?, year=?, gpa=? WHERE id=?");
$stmt->bind_param("ssssds", $d['id'], $d['name'], $d['course'], $d['year'], $d['gpa'], $d['oldId']);
 
if ($stmt->execute()) {
    log_action($conn, "Updated student {$d['name']} ({$d['id']})");
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Duplicate ID or error"]);
}
 