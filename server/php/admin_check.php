<?php
$headers = getallheaders();
$auth = "";
if (isset($headers["Authorization"])) {
    $auth = $headers["Authorization"];
}

$token = str_replace("Bearer ", "", $auth);
$payload = verify_token($token);

if (!$payload || $payload["is_admin"] != 1) {
    $response = [];
    $response["error"] = "unauthorized";
    echo json_encode($response);
    exit();
}
?>