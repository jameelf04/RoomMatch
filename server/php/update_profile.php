<?php
include(__DIR__ . "/log_helper.php");
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
include(__DIR__ . "/auth_check.php");
$userid = $payload["user_id"];
$input = json_decode(file_get_contents("php://input"), true);
$username = $input["username"];

$current_password = $input["current_password"];
$new_password = $input["new_password"];

$sql = "SELECT password_hash FROM users WHERE user_id = ?";
$query = $mysql->prepare($sql);
$query->bind_param("i", $userid);
$query->execute();
$array = $query->get_result();
$user = $array->fetch_assoc();
if (!password_verify($current_password, $user["password_hash"])) {
    $response = [];
    $response["success"] = false;
    $response["message"] = "Current password is incorrect!";
    echo json_encode($response);
    exit();}
if ($new_password != "") {
    $hash = password_hash($new_password, PASSWORD_BCRYPT);
    $sql = "UPDATE users SET username = ?, password_hash = ? WHERE user_id = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("ssi", $username, $hash, $userid);
} else {
    $sql = "UPDATE users SET username = ? WHERE user_id = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("si", $username, $userid);}
if ($query->execute()) {
    $response = [];
    $response["success"] = true;
    $response["username"] = $username;
    echo json_encode($response);
} else {
    $response = [];
    $response["error"] = $mysql->error;
    echo json_encode($response);

}?>