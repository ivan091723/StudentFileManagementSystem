<?php
/* auth.php - shared helper (NEW FILE)
   Put it in the same folder as db.php. It starts the session and tells
   the other files WHO is logged in and whether they are an admin. */
 
@ini_set('display_errors', '0');          // never let PHP warnings break the JSON output
 
require_once __DIR__ . '/db.php';          // gives us $conn (same as your add_student.php used)
 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
function current_user() {
    foreach (['username', 'user', 'user_name', 'name'] as $key) {
        if (!empty($_SESSION[$key]) && is_string($_SESSION[$key])) {
            return $_SESSION[$key];
        }
    }
    return null;
}
 
function current_role() {
    foreach (['role', 'user_role', 'type'] as $key) {
        if (!empty($_SESSION[$key]) && is_string($_SESSION[$key])) {
            return strtolower($_SESSION[$key]);
        }
    }
    return (current_user() === 'admin') ? 'admin' : 'user';
}
 
function is_admin() {
    return current_role() === 'admin';
}
 
/* Writes one line to activity_log. Never crashes the request if the table differs. */
function write_log($conn, $action) {
    try {
        $u = current_user() ?: 'unknown';
        $r = current_role();
        $stmt = $conn->prepare("INSERT INTO activity_log (username, role, action) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $u, $r, $action);
            $stmt->execute();
        }
    } catch (Throwable $e) {
        // ignore: logging must never block the real action
    }
}