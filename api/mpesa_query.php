<?php
/**
 * mpesa_query.php
 * Queries the current status of an STK Push transaction.
 * Used by the frontend to poll for payment completion.
 * Expects POST JSON: { "checkout_request_id": "ws_CO_..." }
 * Returns JSON:      { "status": "completed|pending|failed", "result_desc": "..." }
 */

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../config/mpesa.php';
require_once '../api/mpesa_auth.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['checkout_request_id'])) {
    echo json_encode(['error' => 'checkout_request_id is required']);
    exit;
}

$checkoutRequestId = $data['checkout_request_id'];

// --- First check our local DB — callback may have already updated it ---
try {
    $stmt = $pdo->prepare("
        SELECT status, result_code, result_desc, mpesa_receipt_number, amount
        FROM mpesa_transactions
        WHERE checkout_request_id = ?
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->execute([$checkoutRequestId]);
    $tx = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// If already resolved (callback came in), return from DB
if ($tx && $tx['status'] !== 'pending') {
    echo json_encode([
        'status'                => $tx['status'],
        'result_code'           => $tx['result_code'],
        'result_desc'           => $tx['result_desc'],
        'mpesa_receipt_number'  => $tx['mpesa_receipt_number'],
        'amount'                => $tx['amount'],
    ]);
    exit;
}

// --- Callback not received yet — query Safaricom directly ---
$timestamp = date('YmdHis');
$password  = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

$payload = [
    'BusinessShortCode' => MPESA_SHORTCODE,
    'Password'          => $password,
    'Timestamp'         => $timestamp,
    'CheckoutRequestID' => $checkoutRequestId,
];

try {
    $token = getMpesaAccessToken();
} catch (RuntimeException $e) {
    // If token fails, fall back to DB status
    echo json_encode(['status' => $tx ? $tx['status'] : 'pending', 'result_desc' => 'Awaiting payment...']);
    exit;
}

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => MPESA_API_BASE . '/mpesa/stkpushquery/v1/query',
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT        => 20,
]);

$response  = curl_exec($curl);
$httpCode  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

if ($curlError || $httpCode !== 200) {
    // Network issue — return pending, frontend will retry
    echo json_encode(['status' => 'pending', 'result_desc' => 'Checking payment status...']);
    exit;
}

$result = json_decode($response, true);
$resultCode = isset($result['ResultCode']) ? (int) $result['ResultCode'] : null;
$resultDesc = $result['ResultDesc'] ?? 'Unknown';

// ResultCode 0 = success, 1032 = cancelled by user, others = failed
if ($resultCode === 0) {
    $status = 'completed';
} elseif ($resultCode === null) {
    $status = 'pending';   // still processing
} else {
    $status = 'failed';
}

// Update DB if we now have a resolution
if ($status !== 'pending' && $tx) {
    try {
        $pdo->prepare("
            UPDATE mpesa_transactions
            SET status = ?, result_code = ?, result_desc = ?, updated_at = NOW()
            WHERE checkout_request_id = ?
        ")->execute([$status, $resultCode, $resultDesc, $checkoutRequestId]);

        if ($status === 'completed') {
            $pdo->prepare("
                UPDATE bookings
                SET status = 'confirmed', payment_status = 'paid', updated_at = NOW()
                WHERE id = (SELECT booking_id FROM mpesa_transactions WHERE checkout_request_id = ? LIMIT 1)
            ")->execute([$checkoutRequestId]);
        }
    } catch (PDOException $e) {
        error_log('mpesa_query DB update error: ' . $e->getMessage());
    }
}

echo json_encode([
    'status'      => $status,
    'result_code' => $resultCode,
    'result_desc' => $resultDesc,
]);
