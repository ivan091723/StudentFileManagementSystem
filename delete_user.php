<?php
include "db.php";
require_admin();
$d = json_decode(file_get_contents("php://input"), true);
$id = (int)($d['id'] ?? 0);
 
$find = $conn->prepare("SELECT username, role FROM users WHERE id=?");
$find->bind_param("i", $id);
$find->execute();
$row = $find->get_result()->fetch_assoc();
 
if (!$row) fail("Account not found.");
if ($row['role'] === 'admin') fail("Admin accounts cannot be deleted.");
 
$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$ok = $stmt->execute();
 
if ($ok) {
    log_action($conn, "Deleted account '{$row['username']}'");
}
echo json_encode(["success" => $ok]);
 