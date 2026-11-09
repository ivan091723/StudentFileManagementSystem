<?php
include "db.php";
require_admin();
$result = $conn->query("SELECT id, username, role, action, created_at FROM activity_log ORDER BY id DESC LIMIT 200");
echo json_encode($result->fetch_all(MYSQLI_ASSOC));
 