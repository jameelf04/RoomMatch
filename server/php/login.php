<?php
session_start();
include "connection.php";

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
    $_SESSION["user_id"] = $user["user_id"];
    $_SESSION["username"] = $user["username"];
    echo json_encode(array("success" => true));
} else {
    echo json_encode(array("error" => "invalid email or password"));
}

mysqli_close($conn);
?>