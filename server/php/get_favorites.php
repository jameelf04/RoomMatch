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

$sql = "SELECT furniture_items.*, favorites.fav_id FROM favorites JOIN furniture_items ON favorites.item_id = furniture_items.item_id WHERE favorites.user_id = ? ORDER BY favorites.created_at DESC";
$query = $mysql->prepare($sql);
$query->bind_param("i", $userid);
$query->execute();
$array = $query->get_result();

$items = [];
while ($row = $array->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);

?>