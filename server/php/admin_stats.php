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

$stats = array();

$r1 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM users");
$stats["users"] = mysqli_fetch_assoc($r1)["c"];

$r2 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM room_sessions");
$stats["sessions"] = mysqli_fetch_assoc($r2)["c"];

$r3 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM furniture_items");
$stats["items"] = mysqli_fetch_assoc($r3)["c"];

$r4 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM favorites");
$stats["favorites"] = mysqli_fetch_assoc($r4)["c"];

$r5 = mysqli_query($conn, "SELECT room_type, COUNT(*) AS c FROM room_sessions GROUP BY room_type");
$byRoom = array();
while ($row = mysqli_fetch_assoc($r5)) {
    $byRoom[] = $row;
}
$stats["by_room"] = $byRoom;

$r6 = mysqli_query($conn, "SELECT style_pref, COUNT(*) AS c FROM room_sessions GROUP BY style_pref ORDER BY c DESC");
$byStyle = array();
while ($row = mysqli_fetch_assoc($r6)) {
    $byStyle[] = $row;
}
$stats["by_style"] = $byStyle;

$r7 = mysqli_query($conn, "SELECT furniture_items.name, COUNT(*) AS c FROM favorites JOIN furniture_items ON favorites.item_id = furniture_items.item_id GROUP BY favorites.item_id ORDER BY c DESC LIMIT 5");
$topFavs = array();
while ($row = mysqli_fetch_assoc($r7)) {
    $topFavs[] = $row;
}
$stats["top_favorites"] = $topFavs;

echo json_encode($stats);

mysqli_close($conn);
?>