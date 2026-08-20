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
$itemId = mysqli_real_escape_string($conn, $input["item_id"]);

mysqli_query($conn, "DELETE FROM favorites WHERE item_id = '$itemId'");
mysqli_query($conn, "DELETE FROM match_results WHERE item_id = '$itemId'");

if (mysqli_query($conn, "DELETE FROM furniture_items WHERE item_id = '$itemId'")) {
    echo json_encode(array("success" => true));
} else {
    echo json_encode(array("error" => mysqli_error($conn)));
}

mysqli_close($conn);
?>