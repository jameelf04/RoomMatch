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
    exit();
}
$input = json_decode(file_get_contents("php://input"), true);

$name = $input["name"];
$category = $input["category"];
$room_type = $input["room_type"];
$style = $input["style"];
$color_hex = $input["color_hex"];
$price = $input["price"];
$store_name = $input["store_name"];
$region = $input["region"];
$image_url = $input["image_url"];
$purchase_url = $input["purchase_url"];
$min_room_area = $input["min_room_area"];
if ($min_room_area == "")   {
    $min_room_area = 0;}

if ($input["item_id"] == "" || $input["item_id"] == null) {
    $sql = "INSERT INTO furniture_items(name, category, room_type, style, color_hex, price, store_name, region, image_url, purchase_url, min_room_area) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $query = $mysql->prepare($sql);
    $query->bind_param("ssssdssssss", $name, $category, $room_type, $style, $color_hex, $price, $store_name, $region, $image_url, $purchase_url, $min_room_area);
} else {
    $itemid = $input["item_id"];
    $sql = "UPDATE furniture_items SET name = ?, category = ?, room_type = ?, style = ?, color_hex = ?, price = ?, store_name = ?, region = ?, image_url = ?, purchase_url = ?, min_room_area = ? WHERE item_id = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("ssssdsssssdi", $name, $category, $room_type, $style, $color_hex, $price, $store_name, $region, $image_url, $purchase_url, $min_room_area, $itemid);}
if ($query->execute()) {
    $response = [];
    $response["success"] = true;
    echo json_encode($response);
} else {
    $response = [];
    $response["error"] = $mysql->error;
    echo json_encode($response);}

?>