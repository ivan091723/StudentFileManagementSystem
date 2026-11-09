<?php
include "db.php";
if (isset($_SESSION['username'])) {
    log_action($conn, "Logged out");
}
session_destroy();
echo json_encode(["success" => true]);
 