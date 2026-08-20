<?php
$secret = "roommatch_secret_key_2026";

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function create_token($userId, $username, $isAdmin) {
    global $secret;

    $header = json_encode(array("alg" => "HS256", "typ" => "JWT"));
    $payload = json_encode(array(
        "user_id" => $userId,
        "username" => $username,
        "is_admin" => $isAdmin,
        "exp" => time() + 86400
    ));

    $headerEncoded = base64url_encode($header);
    $payloadEncoded = base64url_encode($payload);

    $signature = hash_hmac("sha256", $headerEncoded . "." . $payloadEncoded, $secret, true);
    $signatureEncoded = base64url_encode($signature);

    return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
}

function verify_token($token) {
    global $secret;

    $parts = explode(".", $token);
    if (count($parts) != 3) {
        return false;
    }

    $headerEncoded = $parts[0];
    $payloadEncoded = $parts[1];
    $signatureEncoded = $parts[2];

    $expected = base64url_encode(hash_hmac("sha256", $headerEncoded . "." . $payloadEncoded, $secret, true));

    if ($signatureEncoded != $expected) {
        return false;
    }

    $payload = json_decode(base64url_decode($payloadEncoded), true);

    if ($payload["exp"] < time()) {
        return false;
    }

    return $payload;
}
?>