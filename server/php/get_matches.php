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
$roomArea = $session["room_area"];
$roomColors = explode(",", $session["dominant_colors"]);
$styleList = explode(",", $stylePref);

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

$sql2 = "SELECT * FROM furniture_items WHERE room_type = '$roomType' AND price <= '$budget'";
if ($roomArea > 0) {
    $sql2 = $sql2 . " AND min_room_area <= '$roomArea'";
}
$result2 = mysqli_query($conn, $sql2);

$items = array();

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
    for ($k = 0; $k < count($styleList); $k++) {
        if ($row["style"] == $styleList[$k]) {
            $styleScore = 1;
        }
    }

    $priceScore = 1 - ($row["price"] / $budget);
    if ($priceScore < 0) {
        $priceScore = 0;
    }

    $regionScore = 0;
    if ($row["region"] == $region) {
        $regionScore = 1;
    } else {
        $regionScore = 0.5;
    }

    $finalScore = ($styleScore * 0.4) + ($colorScore * 0.35) + ($priceScore * 0.15) + ($regionScore * 0.1);

    $explanation = "Recommended because it ";
    $reasons = array();

    if ($styleScore == 1) {
        $reasons[] = "matches your " . $row["style"] . " style";
    }
    if ($colorScore > 0.7) {
        $reasons[] = "complements your room's colors";
    }
    if ($row["price"] <= $budget) {
        $reasons[] = "is within your budget";
    }
    if ($regionScore == 1) {
        $reasons[] = "is available in your region";
    }
    if ($roomArea > 0) {
        $reasons[] = "fits your " . $roomArea . " m2 room";
    }

    if (count($reasons) == 0) {
        $reasons[] = "is a close match to your preferences";
    }

    $explanation = $explanation . implode(", ", $reasons);

    $row["match_score"] = round($finalScore, 4);
    $row["style_score"] = round($styleScore, 4);
    $row["color_score"] = round($colorScore, 4);
    $row["price_score"] = round($priceScore, 4);
    $row["explanation"] = $explanation;

    $items[] = $row;
}

usort($items, function($a, $b) {
    if ($a["match_score"] == $b["match_score"]) {
        return 0;
    }
    return ($a["match_score"] < $b["match_score"]) ? 1 : -1;
});

$topItems = array_slice($items, 0, 9);

$checkSql = "SELECT * FROM match_results WHERE session_id = '$sessionId'";
$checkResult = mysqli_query($conn, $checkSql);

if (mysqli_num_rows($checkResult) == 0) {
    for ($i = 0; $i < count($topItems); $i++) {
        $it = $topItems[$i];
        $itemId = $it["item_id"];
        $ms = $it["match_score"];
        $ss = $it["style_score"];
        $cs = $it["color_score"];
        $ps = $it["price_score"];
        $ex = mysqli_real_escape_string($conn, $it["explanation"]);

        $insertSql = "INSERT INTO match_results (session_id, item_id, match_score, style_score, color_score, price_score, explanation) VALUES ('$sessionId', '$itemId', '$ms', '$ss', '$cs', '$ps', '$ex')";
        mysqli_query($conn, $insertSql);
    }
}

echo json_encode($topItems);

mysqli_close($conn);
?>