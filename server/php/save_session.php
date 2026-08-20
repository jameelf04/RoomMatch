<?php
include "connection.php";
include "jwt.php";

$headers = getallheaders();
$auth = "";
if (isset($headers["Authorization"])) {
    $auth = $headers["Authorization"];
}

$token = str_replace("Bearer ", "", $auth);
$payload = verify_token($token);

if (!$payload) {
    echo json_encode(array("error" => "unauthorized"));
    exit;
}

$userId = $payload["user_id"];

$input = json_decode(file_get_contents("php://input"), true);

$roomType = mysqli_real_escape_string($conn, $input["room_type"]);
$stylePref = mysqli_real_escape_string($conn, $input["style_pref"]);
$budget = mysqli_real_escape_string($conn, $input["budget"]);
$region = mysqli_real_escape_string($conn, $input["region"]);
$colors = mysqli_real_escape_string($conn, $input["dominant_colors"]);
$roomArea = mysqli_real_escape_string($conn, $input["room_area"]);
if ($roomArea == "") {
    $roomArea = 0;
}

$sql = "INSERT INTO room_sessions (user_id, room_type, dominant_colors, style_pref, budget, region, room_area) VALUES ('$userId', '$roomType', '$colors', '$stylePref', '$budget', '$region', '$roomArea')";
if (mysqli_query($conn, $sql)) {
    $sessionId = mysqli_insert_id($conn);
    echo json_encode(array("session_id" => $sessionId));
} else {
    echo json_encode(array("error" => mysqli_error($conn)));
}

mysqli_close($conn);
?>