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

$sql = "SELECT * FROM furniture_items ORDER BY name";
$query = $mysql->prepare($sql);
$query->execute();
$array = $query->get_result();

$items = [];
while ($row = $array->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);

?>