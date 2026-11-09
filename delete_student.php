<?php
include "db.php";
require_admin();
$d = json_decode(file_get_contents("php://input"), true);
 
$stmt = $conn->prepare("DELETE FROM students WHERE id=?");
$stmt->bind_param("s", $d['id']);
$stmt->execute();
 
$name = $d['name'] ?? '';
log_action($conn, "Deleted student $name ({$d['id']})");
echo json_encode(["success" => true]);
 