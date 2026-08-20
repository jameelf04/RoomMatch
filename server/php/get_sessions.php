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

$sql = "SELECT * FROM room_sessions WHERE user_id = '$userId' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$sessions = array();

while ($row = mysqli_fetch_assoc($result)) {
    $sessions[] = $row;
}

echo json_encode($sessions);

mysqli_close($conn);
?>