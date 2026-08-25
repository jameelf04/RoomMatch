<?php
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");

$headers = getallheaders();
$auth = "";
if (isset($headers["Authorization"])) {
    $auth = $headers["Authorization"];
}

$token = str_replace("Bearer ", "", $auth);
$payload = verify_token($token);
if (!$payload) {
    $response = [];
    $response["error"] = "unauthorized";
    echo json_encode($response);
    exit();}
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