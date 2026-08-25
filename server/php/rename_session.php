<?php
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
$headers = getallheaders();
$auth = "";
if (isset($headers["Authorization"])) {
    $auth = $headers["Authorization"];}
$token = str_replace("Bearer ", "", $auth);
$payload = verify_token($token);
if (!$payload) {
    $response = [];
    $response["error"] = "unauthorized";
    echo json_encode($response);
    exit();
}
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