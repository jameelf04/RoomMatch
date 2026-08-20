<?php
include "connection.php";

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

$sql2 = "SELECT * FROM furniture_items WHERE room_type = '$roomType' AND price <= '$budget'";
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

    $styleScore = 0;
    if ($row["style"] == $stylePref) {
        $styleScore = 1;
    } else {
        $styleScore = 0.3;
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
        $reasons[] = "matches your " . $stylePref . " style";
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

    if (count($reasons) == 0) {
        $reasons[] = "is a close match to your preferences";
    }

    $explanation = $explanation . implode(", ", $reasons);

    $row["match_score"] = round($finalScore, 4);
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

echo json_encode($topItems);

mysqli_close($conn);
?>