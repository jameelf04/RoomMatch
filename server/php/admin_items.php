<?php
include(__DIR__ . "/log_helper.php");
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
include(__DIR__ . "/admin_check.php");

$sql = "SELECT * FROM furniture_items ORDER BY item_id DESC";
$query = $mysql->prepare($sql);
$query->execute();
$array = $query->get_result();
$items = [];
while ($row = $array->fetch_assoc()) {
    $items[] = $row;
}
echo json_encode($items); ?>