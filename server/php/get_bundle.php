<?php
include(__DIR__ . "/log_helper.php");
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
include(__DIR__ . "/auth_check.php");

$userid = $payload["user_id"];
$sessionid = $_GET["session"];
$sql = "SELECT * FROM room_sessions WHERE session_id = ?";
$query = $mysql->prepare($sql);
$query->bind_param("i", $sessionid);
$query->execute();
$array = $query->get_result();
$session = $array->fetch_assoc();

if (!$session) {
    $response = [];
    $response["error"] = "session not found";
    echo json_encode($response);
    exit();
}

$room_type = $session["room_type"];
$style_pref = $session["style_pref"];
$budget = $session["budget"];
$region = $session["region"];
$room_area = $session["room_area"];
$room_colors = explode(",", $session["dominant_colors"]);
$style_list = explode(",", $style_pref);
function hex_to_rgb2($hex) {
    $hex = str_replace("#", "", $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return [$r, $g, $b];
}
function color_distance2($rgb1, $rgb2) {
    $dr = $rgb1[0] - $rgb2[0];
    $dg = $rgb1[1] - $rgb2[1];
    $db = $rgb1[2] - $rgb2[2];
    return sqrt($dr * $dr + $dg * $dg + $db * $db);
}
$room_rgb_list = [];
for ($i = 0; $i < count($room_colors); $i++) {
    $room_rgb_list[] = hex_to_rgb2($room_colors[$i]);
}

if ($room_type == "living_room") {
    $order = ["sofa", "coffee_table", "rug", "armchair", "floor_lamp", "side_table"];
} else {
    $order = ["bed", "wardrobe", "dresser", "nightstand", "rug", "bedroom_lamp"];}

$sql = "SELECT * FROM furniture_items WHERE room_type = ? AND min_room_area <= ?";
$area_check = $room_area > 0 ? $room_area : 999999;
$query = $mysql->prepare($sql);
$query->bind_param("sd", $room_type, $area_check);
$query->execute();
$array = $query->get_result();
$scored = [];

while ($row = $array->fetch_assoc()) {
    $item_rgb = hex_to_rgb2($row["color_hex"]);
    $min_distance = 999999;
    for ($i = 0; $i < count($room_rgb_list); $i++) {
        $dist = color_distance2($room_rgb_list[$i], $item_rgb);
        if ($dist < $min_distance) {
            $min_distance = $dist;
        }
    }
    $max_distance = 441.67;
    $color_score = 1 - ($min_distance / $max_distance);
    if ($color_score < 0) {
        $color_score = 0;
    }
    $style_score = 0.3;
    for ($k = 0; $k < count($style_list); $k++) {
        if ($row["style"] == $style_list[$k]) {
            $style_score = 1;
        }
    }
    $region_score = 0.5;
    if ($row["region"] == $region) {
        $region_score = 1;}
    $final_score = ($style_score * 0.45) + ($color_score * 0.40) + ($region_score * 0.15);
    $row["bundle_score"] = round($final_score, 4);
    $scored[] = $row;
}

$bundle = [];
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
    }}
$response = [];
$response["items"] = $bundle;
$response["total"] = $total;
$response["budget"] = $budget;
$response["remaining"] = $budget - $total;
echo json_encode($response); ?>