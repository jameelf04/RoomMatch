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
    exit();
}

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