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

if (!$payload || $payload["is_admin"] != 1) {
    echo json_encode(array("error" => "unauthorized"));
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

$name = mysqli_real_escape_string($conn, $input["name"]);
$category = mysqli_real_escape_string($conn, $input["category"]);
$roomType = mysqli_real_escape_string($conn, $input["room_type"]);
$style = mysqli_real_escape_string($conn, $input["style"]);
$colorHex = mysqli_real_escape_string($conn, $input["color_hex"]);
$price = mysqli_real_escape_string($conn, $input["price"]);
$store = mysqli_real_escape_string($conn, $input["store_name"]);
$region = mysqli_real_escape_string($conn, $input["region"]);
$imageUrl = mysqli_real_escape_string($conn, $input["image_url"]);
$purchaseUrl = mysqli_real_escape_string($conn, $input["purchase_url"]);

if ($input["item_id"] == "" || $input["item_id"] == null) {
    $sql = "INSERT INTO furniture_items (name, category, room_type, style, color_hex, price, store_name, region, image_url, purchase_url) VALUES ('$name', '$category', '$roomType', '$style', '$colorHex', '$price', '$store', '$region', '$imageUrl', '$purchaseUrl')";
} else {
    $itemId = mysqli_real_escape_string($conn, $input["item_id"]);
    $sql = "UPDATE furniture_items SET name = '$name', category = '$category', room_type = '$roomType', style = '$style', color_hex = '$colorHex', price = '$price', store_name = '$store', region = '$region', image_url = '$imageUrl', purchase_url = '$purchaseUrl' WHERE item_id = '$itemId'";
}

if (mysqli_query($conn, $sql)) {
    echo json_encode(array("success" => true));
} else {
    echo json_encode(array("error" => mysqli_error($conn)));
}

mysqli_close($conn);
?>