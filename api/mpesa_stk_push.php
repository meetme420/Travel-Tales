<?php
/**
 * mpesa_stk_push.php
 * Initiates an M-Pesa STK Push (Lipa Na M-Pesa Online) payment request.
 * Expects POST JSON: { "booking_id": int, "phone_number": "2547XXXXXXXX" }
 * Returns JSON:      { "success": true, "checkout_request_id": "..." }
 *                 or { "error": "..." }
 */

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../config/mpesa.php';
require_once '../api/mpesa_auth.php';

// --- Auth check ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['error' => 'Please login to make a payment']);
    exit;
}

// --- Input validation ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['booking_id']) || empty($data['phone_number'])) {
    echo json_encode(['error' => 'booking_id and phone_number are required']);
    exit;
}

$bookingId   = (int) $data['booking_id'];
$phoneNumber = preg_replace('/\s+/', '', $data['phone_number']);

// Normalize phone: strip leading + or 0, ensure starts with 254
if (str_starts_with($phoneNumber, '+')) {
    $phoneNumber = ltrim($phoneNumber, '+');
} elseif (str_starts_with($phoneNumber, '0')) {
    $phoneNumber = '254' . substr($phoneNumber, 1);
}

if (!preg_match('/^2547\d{8}$/', $phoneNumber)) {
    echo json_encode(['error' => 'Invalid phone number. Use format 2547XXXXXXXX or 07XXXXXXXX']);
    exit;
}

// --- Fetch booking ---
try {
    $stmt = $pdo->prepare("
        SELECT b.total_price, b.user_id
        FROM bookings b
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$bookingId, $_SESSION['id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

if (!$booking) {
    echo json_encode(['error' => 'Booking not found or access denied']);
    exit;
}

// Convert USD → KES (M-Pesa only accepts KES)
$amountKes = (int) ceil($booking['total_price'] * USD_TO_KES);

// --- Build STK Push request ---
$timestamp = date('YmdHis');
$password  = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

$payload = [
    'BusinessShortCode' => MPESA_SHORTCODE,
    'Password'          => $password,
    'Timestamp'         => $timestamp,
    'TransactionType'   => MPESA_TRANSACTION_TYPE,
    'Amount'            => $amountKes,
    'PartyA'            => $phoneNumber,
    'PartyB'            => MPESA_SHORTCODE,
    'PhoneNumber'       => $phoneNumber,
    'CallBackURL'       => MPESA_CALLBACK_URL,
    'AccountReference'  => 'TravelTales-' . $bookingId,
    'TransactionDesc'   => 'Travel Tales Booking #' . $bookingId,
];

// --- Get auth token ---
try {
    $token = getMpesaAccessToken();
} catch (RuntimeException $e) {
    echo json_encode(['error' => 'M-Pesa authentication failed: ' . $e->getMessage()]);
    exit;
}

// --- Send STK Push request to Safaricom ---
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => MPESA_API_BASE . '/mpesa/stkpush/v1/processrequest',
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT        => 30,
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

if ($curlError) {
    echo json_encode(['error' => 'cURL error: ' . $curlError]);
    exit;
}

$result = json_decode($response, true);

if ($httpCode !== 200 || empty($result['CheckoutRequestID'])) {
    $errorMessage = $result['errorMessage'] ?? $result['ResponseDescription'] ?? $response;
    echo json_encode(['error' => 'STK Push failed: ' . $errorMessage]);
    exit;
}

// --- Save pending transaction to DB ---
try {
    $stmt = $pdo->prepare("
        INSERT INTO mpesa_transactions
            (booking_id, phone_number, amount, merchant_request_id, checkout_request_id, status, created_at, updated_at)
        VALUES
            (?, ?, ?, ?, ?, 'pending', NOW(), NOW())
    ");
    $stmt->execute([
        $bookingId,
        $phoneNumber,
        $amountKes,
        $result['MerchantRequestID'],
        $result['CheckoutRequestID'],
    ]);
} catch (PDOException $e) {
    // Non-fatal — payment was initiated, DB logging failed
    error_log('mpesa_stk_push DB error: ' . $e->getMessage());
}

echo json_encode([
    'success'             => true,
    'message'             => 'STK Push sent. Please check your phone and enter your M-Pesa PIN.',
    'checkout_request_id' => $result['CheckoutRequestID'],
    'merchant_request_id' => $result['MerchantRequestID'],
    'amount_kes'          => $amountKes,
]);
