<?php
include "db.php";
require_admin();
$conn->query("DELETE FROM activity_log");
echo json_encode(["success" => true]);
 