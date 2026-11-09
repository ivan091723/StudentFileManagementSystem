<?php
include "db.php";
require_admin();
$d = json_decode(file_get_contents("php://input"), true);
$id = (int)($d['id'] ?? 0);
$email = trim($d['email'] ?? '');
$pin = trim($d['pin'] ?? '');
 
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) fail("Please enter a valid email address.");
if ($pin !== '' && !preg_match('/^\d{4}$/', $pin)) fail("PIN must be exactly 4 digits.");
 
$find = $conn->prepare("SELECT username FROM users WHERE id=?");
$find->bind_param("i", $id);
$find->execute();
$row = $find->get_result()->fetch_assoc();
if (!$row) fail("Account not found.");
 
if ($email !== '') {
    $chk = $conn->prepare("SELECT id FROM users WHERE email=? AND id<>?");
    $chk->bind_param("si", $email, $id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) fail("That email is already used by another account.");
}
 
$emailVal = ($email === '') ? null : $email;
 
if ($pin !== '') {
    $stmt = $conn->prepare("UPDATE users SET email=?, pin=? WHERE id=?");
    $stmt->bind_param("ssi", $emailVal, $pin, $id);
} else {
    $stmt = $conn->prepare("UPDATE users SET email=? WHERE id=?");
    $stmt->bind_param("si", $emailVal, $id);
}
$ok = $stmt->execute();
 
if ($ok) {
    $what = $pin !== '' ? "email and password" : "email";
    log_action($conn, "Edited {$what} of account '{$row['username']}'");
}
echo json_encode(["success" => $ok]);
 