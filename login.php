<?php
include "db.php";
$d = json_decode(file_get_contents("php://input"), true);
$login = trim($d['username'] ?? '');
$pin = trim($d['pin'] ?? '');
 
// Sign in with either the username or the email
$stmt = $conn->prepare("SELECT username, role FROM users WHERE (username=? OR email=?) AND pin=?");
$stmt->bind_param("sss", $login, $login, $pin);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
 
if ($row) {
    $_SESSION['username'] = $row['username'];
    $_SESSION['role'] = $row['role'];
    log_action($conn, "Logged in");
    echo json_encode(["success" => true, "role" => $row['role'], "username" => $row['username']]);
} else {
    echo json_encode(["success" => false]);
}
 