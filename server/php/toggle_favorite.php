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

if (!$payload) {
    echo json_encode(array("error" => "unauthorized"));
    exit;
}

$userId = $payload["user_id"];

$input = json_decode(file_get_contents("php://input"), true);
$itemId = mysqli_real_escape_string($conn, $input["item_id"]);

$check = "SELECT * FROM favorites WHERE user_id = '$userId' AND item_id = '$itemId'";
$result = mysqli_query($conn, $check);

if (mysqli_num_rows($result) > 0) {
    mysqli_query($conn, "DELETE FROM favorites WHERE user_id = '$userId' AND item_id = '$itemId'");
    echo json_encode(array("favorited" => false));
} else {
    mysqli_query($conn, "INSERT INTO favorites (user_id, item_id) VALUES ('$userId', '$itemId')");
    echo json_encode(array("favorited" => true));
}

mysqli_close($conn);
?>