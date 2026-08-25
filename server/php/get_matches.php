<?php
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
function hex_to_rgb($hex) {
    $hex = str_replace("#", "", $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return [$r, $g, $b];
}
function color_distance($rgb1, $rgb2) {
    $dr = $rgb1[0] - $rgb2[0];
    $dg = $rgb1[1] - $rgb2[1];
    $db = $rgb1[2] - $rgb2[2];
    return sqrt($dr * $dr + $dg * $dg + $db * $db);
}

$room_rgb_list = [];
for ($i = 0; $i < count($room_colors); $i++) {
    $room_rgb_list[] = hex_to_rgb($room_colors[$i]);
}
$sql = "SELECT * FROM furniture_items WHERE room_type = ? AND price <= ? AND min_room_area <= ?";
$area_check = $room_area > 0 ? $room_area : 999999;
$query = $mysql->prepare($sql);
$query->bind_param("sdd", $room_type, $budget, $area_check);
$query->execute();
$array = $query->get_result();
$items = [];

while ($row = $array->fetch_assoc()) {
    $item_rgb = hex_to_rgb($row["color_hex"]);
    $min_distance = 999999;
    for ($i = 0; $i < count($room_rgb_list); $i++) {
        $dist = color_distance($room_rgb_list[$i], $item_rgb);
        if ($dist < $min_distance) {
            $min_distance = $dist;
        } }
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

    $price_score = 1 - ($row["price"] / $budget);
    if ($price_score < 0) {
        $price_score = 0;}

    $region_score = 0.5;
    if ($row["region"] == $region) {
        $region_score = 1;}

    $final_score = ($style_score * 0.4) + ($color_score * 0.35) + ($price_score * 0.15) + ($region_score * 0.1);

    $reasons = [];
    if ($style_score == 1) {
        $reasons[] = "matches your " . $row["style"] . " style";
    }
    if ($color_score > 0.7) {
        $reasons[] = "complements your room's colors";
    }
    if ($row["price"] <= $budget) {
        $reasons[] = "is within your budget";
    }
    if ($region_score == 1) {
        $reasons[] = "is available in your region";
    }
    if ($room_area > 0) {
        $reasons[] = "fits your " . $room_area . " m2 room";
    }

    if (count($reasons) == 0) {
        $reasons[] = "is a close match to your preferences";
    }

    $row["match_score"] = round($final_score, 4);
    $row["style_score"] = round($style_score, 4);
    $row["color_score"] = round($color_score, 4);
    $row["price_score"] = round($price_score, 4);
    $row["explanation"] = "Recommended because it " . implode(", ", $reasons);

    $items[] = $row;
}
usort($items, function($a, $b) {
    if ($a["match_score"] == $b["match_score"]) {
        return 0;
    }
    return ($a["match_score"] < $b["match_score"]) ? 1 : -1;
});

$top_items = array_slice($items, 0, 9);
$sql = "SELECT * FROM match_results WHERE session_id = ?";
$query = $mysql->prepare($sql);
$query->bind_param("i", $sessionid);
$query->execute();
$check = $query->get_result();
if ($check->num_rows == 0) {
    for ($i = 0; $i < count($top_items); $i++) {
        $it = $top_items[$i];
        $sql = "INSERT INTO match_results(session_id, item_id, match_score, style_score, color_score, price_score, explanation) VALUES(?, ?, ?, ?, ?, ?, ?)";
        $query = $mysql->prepare($sql);
        $query->bind_param("iidddds", $sessionid, $it["item_id"], $it["match_score"], $it["style_score"], $it["color_score"], $it["price_score"], $it["explanation"]);
        $query->execute();
    }
}
echo json_encode($top_items);

?>