<?php
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
include(__DIR__ . "/auth_check.php");

$userid = $payload["user_id"];

$sql = "SELECT username, email, created_at FROM users WHERE user_id = ?";
$query = $mysql->prepare($sql);
$query->bind_param("i", $userid);
$query->execute();
$array = $query->get_result();
$user = $array->fetch_assoc();

echo json_encode($user);
?>