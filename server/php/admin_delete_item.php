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

if (!$payload || $payload["is_admin"] != 1) {
    $response = [];
    $response["error"] = "unauthorized";
    echo json_encode($response);
    exit();}
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