<?php

$secret = "roommatch_secret_key_2026";

function b64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function b64url_decode($data) {
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

    $header_enc = b64url_encode($header);
    $payload_enc = b64url_encode($payload);

    $sig = hash_hmac("sha256", $header_enc . "." . $payload_enc, $secret, true);
    $sig_enc = b64url_encode($sig);

    return $header_enc . "." . $payload_enc . "." . $sig_enc;
}

function verify_token($token) {
    global $secret;

    $parts = explode(".", $token);
    if (count($parts) != 3) {
        return false;
    }

    $header_enc = $parts[0];
    $payload_enc = $parts[1];
    $sig_enc = $parts[2];

    $expected = b64url_encode(hash_hmac("sha256", $header_enc . "." . $payload_enc, $secret, true));

    if ($sig_enc != $expected) {
        return false;
    }

    $payload = json_decode(b64url_decode($payload_enc), true);

    if ($payload["exp"] < time()) {
        return false;
    }

    return $payload;
}

?>