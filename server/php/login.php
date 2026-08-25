<?php
include(__DIR__ . "/log_helper.php");
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");

$input = json_decode(file_get_contents("php://input"), true);
$email = $input["email"];
$password = $input["password"];
$sql = "SELECT * FROM users WHERE email = ?";
$query = $mysql->prepare($sql);
$query->bind_param("s", $email);
$query->execute();
$array = $query->get_result();
$user = $array->fetch_assoc();
if (!$user) {
    $response = [];
    $response["success"] = false;
    $response["message"] = "Invalid email or password!";
    echo json_encode($response);
    exit();}
if (password_verify($password, $user["password_hash"])) {
    $token = create_token($user["user_id"], $user["username"], $user["is_admin"]);
    $response = [];
    $response["success"] = true;
    $response["token"] = $token;
    $response["username"] = $user["username"];
    $response["is_admin"] = $user["is_admin"];
    echo json_encode($response);
} else {
    $response = [];
    $response["success"] = false;
    $response["message"] = "Invalid email or password!";
    echo json_encode($response);}
?>