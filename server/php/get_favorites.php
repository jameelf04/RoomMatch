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

$sql = "SELECT furniture_items.*, favorites.fav_id FROM favorites JOIN furniture_items ON favorites.item_id = furniture_items.item_id WHERE favorites.user_id = '$userId' ORDER BY favorites.created_at DESC";
$result = mysqli_query($conn, $sql);

$items = array();

while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

echo json_encode($items);

mysqli_close($conn);
?>