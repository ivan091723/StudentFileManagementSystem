<?php
session_start();
mysqli_report(MYSQLI_REPORT_OFF);
 
$conn = new mysqli("localhost", "root", "", "student_db");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}
header("Content-Type: application/json");
 
function fail($msg, $code = 200) {
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $msg]);
    exit;
}
 
function require_login() {
    if (!isset($_SESSION['role'])) {
        fail("Login required", 401);
    }
}
 
function require_admin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        fail("Admin only", 403);
    }
}
 
function log_action($conn, $action, $user = null, $role = null) {
    $u = $user ?? ($_SESSION['username'] ?? 'guest');
    $r = $role ?? ($_SESSION['role'] ?? 'none');
    $stmt = $conn->prepare("INSERT INTO activity_log (username, role, action) VALUES (?,?,?)");
    $stmt->bind_param("sss", $u, $r, $action);
    $stmt->execute();
}
 