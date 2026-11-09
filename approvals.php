<?php
header('Content-Type: application/json');
require_once 'auth.php';
 
if (!current_user() || !is_admin()) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Admins only"]);
    exit;
}
 
$d = json_decode(file_get_contents("php://input"), true);
if (!is_array($d)) { $d = $_POST; }
 
$action = $d['action'] ?? ($_GET['action'] ?? '');
 
try {
    if ($action === 'list') {
        $res  = $conn->query(
            "SELECT id, name, course, year, gpa, created_by
             FROM students WHERE status = 'pending' ORDER BY id"
        );
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $r['gpa'] = (float)$r['gpa'];
            $rows[] = $r;
        }
        echo json_encode($rows);
        exit;
    }
 
    if ($action === 'approve' || $action === 'reject') {
        $id     = $d['id'] ?? '';
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $admin  = current_user();
 
        $stmt = $conn->prepare(
            "UPDATE students SET status = ?, approved_by = ?, approved_at = NOW()
             WHERE id = ? AND status = 'pending'"
        );
        $stmt->bind_param("sss", $status, $admin, $id);
        $stmt->execute();
 
        $ok = $stmt->affected_rows > 0;
        if ($ok) {
            write_log($conn, ($action === 'approve' ? "Approved" : "Rejected") . " student $id");
        }
        echo json_encode(["success" => $ok]);
        exit;
    }
 
    echo json_encode(["success" => false, "error" => "Unknown action"]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server error"]);
}