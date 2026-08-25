<?php
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
include(__DIR__ . "/auth_check.php");

$userid = $payload["user_id"];
$input = json_decode(file_get_contents("php://input"), true);
$sessionid = $input["session_id"];
$nickname = $input["nickname"];

$sql = "UPDATE room_sessions SET nickname = ? WHERE session_id = ? AND user_id = ?";
$query = $mysql->prepare($sql);
$query->bind_param("sii", $nickname, $sessionid, $userid);
if ($query->execute()) {
    $response = [];
    $response["success"] = true;
    echo json_encode($response);
} else {
    $response = [];
    $response["error"] = $mysql->error;
    echo json_encode($response);}?>