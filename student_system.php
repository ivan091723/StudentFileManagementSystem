<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admins only']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'list') {
    $res = $conn->query(
        "SELECT id, name, course, year, gpa, created_by
         FROM students WHERE status = 'pending' ORDER BY id"
    );
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    exit;
}

if ($action === 'approve' || $action === 'reject') {
    $id     = $_POST['id'] ?? '';
    $status = $action === 'approve' ? 'approved' : 'rejected';
    $admin  = $_SESSION['username'];

    $stmt = $conn->prepare(
        "UPDATE students SET status = ?, approved_by = ?, approved_at = NOW()
         WHERE id = ? AND status = 'pending'"
    );
    $stmt->bind_param("sss", $status, $admin, $id);
    $stmt->execute();

    echo json_encode(['success' => $stmt->affected_rows > 0]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);