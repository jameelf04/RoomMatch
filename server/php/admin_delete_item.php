<?php
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
include(__DIR__ . "/admin_check.php");

$input = json_decode(file_get_contents("php://input"), true);
$itemid = $input["item_id"];
$sql = "DELETE FROM favorites WHERE item_id = ?";
$query = $mysql->prepare($sql);
$query->bind_param("i", $itemid);
$query->execute();

$sql = "DELETE FROM match_results WHERE item_id = ?";
$query = $mysql->prepare($sql);
$query->bind_param("i", $itemid);
$query->execute();
$sql = "DELETE FROM furniture_items WHERE item_id = ?";
$query = $mysql->prepare($sql);
$query->bind_param("i", $itemid);

if ($query->execute()) {
    $response = [];
    $response["success"] = true;
    echo json_encode($response);
} else {
    $response = [];
    $response["error"] = $mysql->error;
    echo json_encode($response);}

?>