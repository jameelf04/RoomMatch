<?php
include "connection.php";
include "jwt.php";

$input = json_decode(file_get_contents("php://input"), true);

$email = mysqli_real_escape_string($conn, $input["email"]);
$password = $input["password"];

$sql = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo json_encode(array("error" => "invalid email or password"));
    exit;
}

if (password_verify($password, $user["password_hash"])) {
    $token = create_token($user["user_id"], $user["username"], $user["is_admin"]);
    echo json_encode(array("success" => true, "token" => $token, "username" => $user["username"], "is_admin" => $user["is_admin"]));
} else {
    echo json_encode(array("error" => "invalid email or password"));
}

mysqli_close($conn);
?>