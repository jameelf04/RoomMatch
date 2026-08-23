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
    $response["success"] = false;
    $response["message"] = "Unauthorized!";
    echo json_encode($response);
    exit();
}

$userid = $payload["user_id"];

$input = json_decode(file_get_contents("php://input"), true);

$room_type = $input["room_type"];
$style_pref = $input["style_pref"];
$budget = $input["budget"];
$region = $input["region"];
$colors = $input["dominant_colors"];
$room_area = $input["room_area"];
if ($room_area == "") {
    $room_area = 0;
}

$sql = "INSERT INTO room_sessions(user_id, room_type, dominant_colors, style_pref, budget, region, room_area) VALUES(?, ?, ?, ?, ?, ?, ?)";
$query = $mysql->prepare($sql);
$query->bind_param("isssdsd", $userid, $room_type, $colors, $style_pref, $budget, $region, $room_area);
$query->execute();

$sessionid = $mysql->insert_id;

$response = [];
$response["session_id"] = $sessionid;
echo json_encode($response);

?>