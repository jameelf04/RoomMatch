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

$sessionId = $_GET["session"];

$sql = "SELECT * FROM room_sessions WHERE session_id = '$sessionId'";
$result = mysqli_query($conn, $sql);
$session = mysqli_fetch_assoc($result);

if (!$session) {
    echo json_encode(array("error" => "session not found"));
    exit;
}

$roomType = $session["room_type"];
$stylePref = $session["style_pref"];
$budget = $session["budget"];
$region = $session["region"];
$roomColors = explode(",", $session["dominant_colors"]);

function hexToRgb($hex) {
    $hex = str_replace("#", "", $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return array($r, $g, $b);
}

function colorDistance($rgb1, $rgb2) {
    $dr = $rgb1[0] - $rgb2[0];
    $dg = $rgb1[1] - $rgb2[1];
    $db = $rgb1[2] - $rgb2[2];
    return sqrt($dr * $dr + $dg * $dg + $db * $db);
}

$roomRgbList = array();
for ($i = 0; $i < count($roomColors); $i++) {
    $roomRgbList[] = hexToRgb($roomColors[$i]);
}

if ($roomType == "living_room") {
    $order = array("sofa", "coffee_table", "rug", "armchair", "floor_lamp", "side_table");
} else {
    $order = array("bed", "wardrobe", "dresser", "nightstand", "rug", "bedroom_lamp");
}

$sql2 = "SELECT * FROM furniture_items WHERE room_type = '$roomType'";
$result2 = mysqli_query($conn, $sql2);

$scored = array();

while ($row = mysqli_fetch_assoc($result2)) {
    $itemRgb = hexToRgb($row["color_hex"]);

    $minDistance = 999999;
    for ($i = 0; $i < count($roomRgbList); $i++) {
        $dist = colorDistance($roomRgbList[$i], $itemRgb);
        if ($dist < $minDistance) {
            $minDistance = $dist;
        }
    }

    $maxDistance = 441.67;
    $colorScore = 1 - ($minDistance / $maxDistance);
    if ($colorScore < 0) {
        $colorScore = 0;
    }

    $styleScore = 0.3;
    if ($row["style"] == $stylePref) {
        $styleScore = 1;
    }

    $regionScore = 0.5;
    if ($row["region"] == $region) {
        $regionScore = 1;
    }

    $finalScore = ($styleScore * 0.45) + ($colorScore * 0.40) + ($regionScore * 0.15);

    $row["bundle_score"] = round($finalScore, 4);
    $scored[] = $row;
}

$bundle = array();
$total = 0;

for ($i = 0; $i < count($order); $i++) {
    $cat = $order[$i];
    $best = null;

    for ($j = 0; $j < count($scored); $j++) {
        $it = $scored[$j];
        if ($it["category"] != $cat) {
            continue;
        }
        if ($total + $it["price"] > $budget) {
            continue;
        }
        if ($best == null || $it["bundle_score"] > $best["bundle_score"]) {
            $best = $it;
        }
    }

    if ($best != null) {
        $bundle[] = $best;
        $total = $total + $best["price"];
    }
}

echo json_encode(array(
    "items" => $bundle,
    "total" => $total,
    "budget" => $budget,
    "remaining" => $budget - $total
));

mysqli_close($conn);
?>