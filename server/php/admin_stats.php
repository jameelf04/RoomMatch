<?php
include(__DIR__ . "/log_helper.php");
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
include(__DIR__ . "/admin_check.php");

$stats = [];
$sql = "SELECT COUNT(*) AS c FROM users";
$query = $mysql->prepare($sql);
$query->execute();
$stats["users"] = $query->get_result()->fetch_assoc()["c"];

$sql = "SELECT COUNT(*) AS c FROM room_sessions";
$query = $mysql->prepare($sql);
$query->execute();
$stats["sessions"] = $query->get_result()->fetch_assoc()["c"];

$sql = "SELECT COUNT(*) AS c FROM furniture_items";
$query = $mysql->prepare($sql);
$query->execute();
$stats["items"] = $query->get_result()->fetch_assoc()["c"];
$sql = "SELECT COUNT(*) AS c FROM favorites";
$query = $mysql->prepare($sql);
$query->execute();
$stats["favorites"] = $query->get_result()->fetch_assoc()["c"];

$sql = "SELECT room_type, COUNT(*) AS c FROM room_sessions GROUP BY room_type";
$query = $mysql->prepare($sql);
$query->execute();
$array = $query->get_result();
$by_room = [];
while ($row = $array->fetch_assoc()) {
    $by_room[] = $row;
}

$stats["by_room"] = $by_room;
$sql = "SELECT style_pref, COUNT(*) AS c FROM room_sessions GROUP BY style_pref ORDER BY c DESC";
$query = $mysql->prepare($sql);
$query->execute();
$array = $query->get_result();
$by_style = [];
while ($row = $array->fetch_assoc()) {
    $by_style[] = $row;}

$stats["by_style"] = $by_style;

$sql = "SELECT furniture_items.name, COUNT(*) AS c FROM favorites JOIN furniture_items ON favorites.item_id = furniture_items.item_id GROUP BY favorites.item_id ORDER BY c DESC LIMIT 5";
$query = $mysql->prepare($sql);
$query->execute();
$array = $query->get_result();
$top_favorites = [];
while ($row = $array->fetch_assoc()) {
    $top_favorites[] = $row;
}
$stats["top_favorites"] = $top_favorites;
echo json_encode($stats);

?>