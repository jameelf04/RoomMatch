<?php
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
include(__DIR__ . "/auth_check.php");

$userid = $payload["user_id"];
$input = json_decode(file_get_contents("php://input"), true);
$itemid = $input["item_id"];
$sql = "SELECT fav_id FROM favorites WHERE user_id = ? AND item_id = ?";
$query = $mysql->prepare($sql);
$query->bind_param("ii", $userid, $itemid);

$query->execute();
$array = $query->get_result();

if ($array->num_rows > 0) {
    $sql = "DELETE FROM favorites WHERE user_id = ? AND item_id = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("ii", $userid, $itemid);
    $query->execute();
    $response = [];
    $response["favorited"] = false;
    echo json_encode($response);
} else {
    $sql = "INSERT INTO favorites(user_id, item_id) VALUES(?, ?)";
    $query = $mysql->prepare($sql);
    $query->bind_param("ii", $userid, $itemid);
    $query->execute();
    $response = [];
    $response["favorited"] = true;
    echo json_encode($response);}
?>