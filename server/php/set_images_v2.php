<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = null;
$db_name = "roommatch";

$mysql = new mysqli($db_host, $db_user, $db_pass, $db_name);

$dir = "../../client/images/furniture/";
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$photos = [
    1239298, 30386991, 6758245, 4112598, 10408437,
    7018400, 276551, 4469171, 6580416, 6438762,
    7027720, 13675290, 5824522, 6970077, 2440471,
    106936, 19699765, 6588592, 33349413, 12127447,
    6480707, 3958563, 4857775, 3105219,
    7598137, 2029694, 1571453, 3965512, 6782348,
    17386986, 6034060, 18700883, 6970059, 6958126,
    4947736, 19846384, 2724749, 4846097,
    707579, 2082092, 29252558, 1743229,
    1648776, 2631746, 1329711,
    2062431, 90317, 1454806
];

$sql = "SELECT item_id FROM furniture_items ORDER BY item_id";
$query = $mysql->prepare($sql);
$query->execute();
$array = $query->get_result();

$i = 0;
while ($row = $array->fetch_assoc()) {
    $itemid = $row["item_id"];
    $photoid = $photos[$i % count($photos)];
    $url = "https://images.pexels.com/photos/" . $photoid . "/pexels-photo-" . $photoid . ".jpeg?auto=compress&cs=tinysrgb&w=600";
    $filename = "item_" . $itemid . ".jpg";
    $path = $dir . $filename;

    $img = @file_get_contents($url);
    if ($img != false && strlen($img) > 5000) {
        file_put_contents($path, $img);
        echo "saved " . $filename . "<br>";
    } else {
        echo "failed " . $filename . " (photo " . $photoid . ")<br>";
    }

    $i = $i + 1;
}

echo "<br>done";

$mysql->close();
?>