<?php

$secret = "roommatch_secret_key_2026";

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function create_token($userid, $username, $is_admin) {
    global $secret;

    $header = json_encode(["alg" => "HS256", "typ" => "JWT"]);
    $payload = json_encode([
        "user_id" => $userid,
        "username" => $username,
        "is_admin" => $is_admin,
        "exp" => time() + 86400
    ]);

    $header_encoded = base64url_encode($header);
    $payload_encoded = base64url_encode($payload);

    $signature = hash_hmac("sha256", $header_encoded . "." . $payload_encoded, $secret, true);
    $signature_encoded = base64url_encode($signature);

    return $header_encoded . "." . $payload_encoded . "." . $signature_encoded;
}

function verify_token($token) {
    global $secret;

    $parts = explode(".", $token);
    if (count($parts) != 3) {
        return false;
    }

    $header_encoded = $parts[0];
    $payload_encoded = $parts[1];
    $signature_encoded = $parts[2];

    $expected = base64url_encode(hash_hmac("sha256", $header_encoded . "." . $payload_encoded, $secret, true));

    if ($signature_encoded != $expected) {
        return false;
    }

    $payload = json_decode(base64url_decode($payload_encoded), true);

    if ($payload["exp"] < time()) {
        return false;
    }

    return $payload;
}

?>