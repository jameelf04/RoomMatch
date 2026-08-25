<?php
include(__DIR__ . "/log_helper.php");
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
include(__DIR__ . "/auth_check.php");

$userid = $payload["user_id"];
$sql = "SELECT * FROM room_sessions WHERE user_id = ? ORDER BY created_at DESC";
$query = $mysql->prepare($sql);
$query->bind_param("i", $userid);
$query->execute();
$array = $query->get_result();

$sessions = [];
while ($row = $array->fetch_assoc()) {
    $sessions[] = $row;
}
echo json_encode($sessions);
?>