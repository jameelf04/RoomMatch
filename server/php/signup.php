<?php
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
$input = json_decode(file_get_contents("php://input"), true);

$username = $input["username"];
$email = $input["email"];
$password = $input["password"];
$sql = "SELECT user_id FROM users WHERE email = ?";

$query = $mysql->prepare($sql);
$query->bind_param("s", $email);
$query->execute();
$array = $query->get_result();

if ($array->num_rows > 0) {
    $response = [];
    $response["success"] = false;
    $response["message"] = "Email already registered!";
    echo json_encode($response);
    exit();}
$hash = password_hash($password, PASSWORD_BCRYPT);
$sql = "INSERT INTO users(username, email, password_hash) VALUES(?, ?, ?)";
$query = $mysql->prepare($sql);
$query->bind_param("sss", $username, $email, $hash);

$query->execute();
$userid = $mysql->insert_id;
$token = create_token($userid, $username, 0);
$response = [];
$response["success"] = true;
$response["token"] = $token;
$response["username"] = $username;
$response["is_admin"] = 0;
echo json_encode($response);

?>