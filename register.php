<?php
include "db.php";
$d = json_decode(file_get_contents("php://input"), true);
$username = trim($d['username'] ?? '');
$email = trim($d['email'] ?? '');
$pin = trim($d['pin'] ?? '');
 
if (strlen($username) < 3) fail("Username must be at least 3 characters.");
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail("Please enter a valid email address.");
if (!preg_match('/^\d{4}$/', $pin)) fail("PIN must be exactly 4 digits.");
 
$chk = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
$chk->bind_param("ss", $username, $email);
$chk->execute();
if ($chk->get_result()->num_rows > 0) fail("That username or email is already taken.");
 
// New accounts are always regular users
$stmt = $conn->prepare("INSERT INTO users (username, pin, email, role) VALUES (?,?,?,'user')");
$stmt->bind_param("sss", $username, $pin, $email);
$ok = $stmt->execute();
 
if ($ok) {
    log_action($conn, "Created a new account", $username, "user");
}
echo json_encode(["success" => $ok]);
 