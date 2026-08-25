<?php
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
include(__DIR__ . "/auth_check.php");

$userid = $payload["user_id"];
$sql = "SELECT furniture_items.*, favorites.fav_id FROM favorites JOIN furniture_items ON favorites.item_id = furniture_items.item_id WHERE favorites.user_id = ? ORDER BY favorites.created_at DESC";
$query = $mysql->prepare($sql);
$query->bind_param("i", $userid);
$query->execute();
$array = $query->get_result();

$items = [];
while ($row = $array->fetch_assoc()) {
    $items[] = $row;}
echo json_encode($items);

?>