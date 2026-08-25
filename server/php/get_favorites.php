<?php
include(__DIR__ . "/log_helper.php");
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
include(__DIR__ . "/auth_check.php");

$userid = $payload["user_id"];

$sql = "SELECT item_id FROM favorites WHERE user_id = ? ORDER BY created_at DESC";
$query = $mysql->prepare($sql);
$query->bind_param("i", $userid);
$query->execute();
$array = $query->get_result();

$items = [];
while ($row = $array->fetch_assoc()) {
    $sql2 = "SELECT * FROM furniture_items WHERE item_id = ?";
    $query2 = $mysql->prepare($sql2);
    $query2->bind_param("i", $row["item_id"]);
    $query2->execute();
    $array2 = $query2->get_result();
    $item = $array2->fetch_assoc();

    if ($item) {
        $items[] = $item;
    }
}

echo json_encode($items);
?>