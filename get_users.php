<?php
include "db.php";
require_admin();
// The PIN / password is never sent to the browser
$result = $conn->query("SELECT id, username, email, role FROM users ORDER BY role, username");
echo json_encode($result->fetch_all(MYSQLI_ASSOC));
 