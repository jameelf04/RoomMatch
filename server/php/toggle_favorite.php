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
    echo json_encode($response);
}

?>