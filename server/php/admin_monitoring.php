<?php
include(__DIR__ . "/connection.php");
include(__DIR__ . "/jwt.php");
include(__DIR__ . "/admin_check.php");

$stats = [];

$sql = "SELECT COUNT(*) AS c FROM request_logs WHERE created_at >= CURDATE()";
$query = $mysql->prepare($sql);
$query->execute();
$stats["requests_today"] = (int) $query->get_result()->fetch_assoc()["c"];

$sql = "SELECT COUNT(*) AS c FROM request_logs WHERE created_at >= CURDATE() AND status_code >= 400";
$query = $mysql->prepare($sql);
$query->execute();
$errors_today = (int) $query->get_result()->fetch_assoc()["c"];
$stats["errors_today"] = $errors_today;

if ($stats["requests_today"] > 0) {
    $stats["error_rate_today"] = round(($errors_today / $stats["requests_today"]) * 100, 2);
} else {
    $stats["error_rate_today"] = 0;
}

$sql = "SELECT AVG(response_time_ms) AS avg_ms FROM request_logs WHERE created_at >= CURDATE()";
$query = $mysql->prepare($sql);
$query->execute();
$avg_row = $query->get_result()->fetch_assoc();
$stats["avg_response_ms"] = $avg_row["avg_ms"] ? (int) round($avg_row["avg_ms"]) : 0;

$sql = "SELECT response_time_ms FROM request_logs WHERE created_at >= CURDATE() ORDER BY response_time_ms ASC";
$query = $mysql->prepare($sql);
$query->execute();
$array = $query->get_result();
$times = [];
while ($row = $array->fetch_assoc()) {
    $times[] = (int) $row["response_time_ms"];
}
if (count($times) > 0) {
    $p95_index = (int) floor(0.95 * (count($times) - 1));
    $stats["p95_response_ms"] = $times[$p95_index];
} else {
    $stats["p95_response_ms"] = 0;
}

$sql = "SELECT COUNT(*) AS total, SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) AS errors FROM request_logs WHERE created_at >= (NOW() - INTERVAL 1 HOUR)";
$query = $mysql->prepare($sql);
$query->execute();
$hour_row = $query->get_result()->fetch_assoc();
$hour_total = (int) $hour_row["total"];
$hour_errors = (int) $hour_row["errors"];
$hour_error_rate = $hour_total > 0 ? round(($hour_errors / $hour_total) * 100, 2) : 0;

$stats["alert_active"] = $hour_error_rate > 5;
$stats["alert_message"] = "Error rate over last hour exceeded 5% threshold (" . $hour_error_rate . "%)";

$sql = "SELECT endpoint, status_code, response_time_ms, created_at FROM request_logs WHERE status_code >= 400 ORDER BY created_at DESC LIMIT 10";
$query = $mysql->prepare($sql);
$query->execute();
$array = $query->get_result();
$recent_errors = [];
while ($row = $array->fetch_assoc()) {
    $recent_errors[] = $row;
}
$stats["recent_errors"] = $recent_errors;

echo json_encode($stats);
?>