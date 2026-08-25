<?php
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
include(__DIR__ . "/auth_check.php");

$userid = $payload["user_id"];
$sessionid = $_GET["session"];
$sql = "SELECT * FROM room_sessions WHERE session_id = ?";
$query = $mysql->prepare($sql);
$query->bind_param("i", $sessionid);
$query->execute();
$array = $query->get_result();
$session = $array->fetch_assoc();

if (!$session) {
    $response = [];
    $response["error"] = "session not found";
    echo json_encode($response);
    exit();}
echo json_encode($session);?>