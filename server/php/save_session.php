<?php
include "connection.php";

$input = json_decode(file_get_contents("php://input"), true);

$roomType = mysqli_real_escape_string($conn, $input["room_type"]);
$stylePref = mysqli_real_escape_string($conn, $input["style_pref"]);
$budget = mysqli_real_escape_string($conn, $input["budget"]);
$region = mysqli_real_escape_string($conn, $input["region"]);
$colors = mysqli_real_escape_string($conn, $input["dominant_colors"]);

$sql = "INSERT INTO room_sessions (room_type, dominant_colors, style_pref, budget, region) VALUES ('$roomType', '$colors', '$stylePref', '$budget', '$region')";

if (mysqli_query($conn, $sql)) {
    $sessionId = mysqli_insert_id($conn);
    echo json_encode(array("session_id" => $sessionId));
} else {
    echo json_encode(array("error" => mysqli_error($conn)));
}

mysqli_close($conn);
?>