<?php
/**
 * mpesa_auth.php
 * Fetches and returns a Safaricom Daraja OAuth2 access token.
 * Caches it in the session to avoid hitting the API on every request.
 */

require_once '../config/mpesa.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

function getMpesaAccessToken() {

    // Return cached token if still valid (expires in ~3600s, we use 3500s to be safe)
    if (
        isset($_SESSION['mpesa_token'], $_SESSION['mpesa_token_expiry']) &&
        time() < $_SESSION['mpesa_token_expiry']
    ) {
        return $_SESSION['mpesa_token'];
    }

    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
    $url = MPESA_API_BASE . '/oauth/v1/generate?grant_type=client_credentials';

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => $url,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Basic ' . $credentials,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false, // TEMPORARY for debugging
        CURLOPT_SSL_VERIFYHOST => 0,     // TEMPORARY for debugging
        CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4, // Force IPv4 routing (sandbox sometimes hangs on IPv6)
        CURLOPT_TIMEOUT        => 30,
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($curlError) {
        throw new RuntimeException('cURL error fetching M-Pesa token: ' . $curlError);
    }

    if ($httpCode !== 200) {
        throw new RuntimeException('M-Pesa auth failed (HTTP ' . $httpCode . '): ' . $response);
    }

    $data = json_decode($response, true);

    if (empty($data['access_token'])) {
        throw new RuntimeException('No access_token in M-Pesa auth response: ' . $response);
    }

    // Cache in session
    $_SESSION['mpesa_token']        = $data['access_token'];
    $_SESSION['mpesa_token_expiry'] = time() + 3500;

    return $data['access_token'];
}
