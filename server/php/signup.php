<?php
include "connection.php";
include "jwt.php";

$input = json_decode(file_get_contents("php://input"), true);

$username = mysqli_real_escape_string($conn, $input["username"]);
$email = mysqli_real_escape_string($conn, $input["email"]);
$password = $input["password"];

$check = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $check);

if (mysqli_num_rows($result) > 0) {
    echo json_encode(array("error" => "email already registered"));
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$sql = "INSERT INTO users (username, email, password_hash) VALUES ('$username', '$email', '$hash')";

if (mysqli_query($conn, $sql)) {
    $userId = mysqli_insert_id($conn);
    $token = create_token($userId, $username, 0);
    echo json_encode(array("success" => true, "token" => $token, "username" => $username, "is_admin" => 0));
} else {
    echo json_encode(array("error" => mysqli_error($conn)));
}

mysqli_close($conn);
?>