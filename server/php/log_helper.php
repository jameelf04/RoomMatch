<?php

$log_start_time = microtime(true);
ob_start();

function rm_log_request() {
    global $log_start_time, $mysql;

    $output = ob_get_clean();
    $elapsed_ms = (int) round((microtime(true) - $log_start_time) * 1000);
    $endpoint = basename($_SERVER["SCRIPT_NAME"]);
    $status_code = 200;

    $fatal = error_get_last();
    if ($fatal && in_array($fatal["type"], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $status_code = 500;
    } else {
        $decoded = json_decode($output, true);
        if (is_array($decoded)) {
            if ((isset($decoded["success"]) && $decoded["success"] == false) || isset($decoded["error"])) {
                $status_code = 400;
            }
        }
    }

    if (isset($mysql) && $mysql instanceof mysqli && !$mysql->connect_error) {
        $sql = "INSERT INTO request_logs(endpoint, status_code, response_time_ms) VALUES(?, ?, ?)";
        $query = $mysql->prepare($sql);
        if ($query) {
            $query->bind_param("sii", $endpoint, $status_code, $elapsed_ms);
            $query->execute();
        }
    }

    echo $output;
}

register_shutdown_function("rm_log_request");
?>