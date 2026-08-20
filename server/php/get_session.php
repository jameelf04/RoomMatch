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

$sessionId = $_GET["session"];

$sql = "SELECT * FROM room_sessions WHERE session_id = '$sessionId'";
$result = mysqli_query($conn, $sql);
$session = mysqli_fetch_assoc($result);

if (!$session) {
    echo json_encode(array("error" => "session not found"));
    exit;
}

echo json_encode($session);

mysqli_close($conn);
?>