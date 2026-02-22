<?php
/**
 * mpesa_callback.php
 * Receives the Safaricom STK Push payment callback (POST request).
 * Updates mpesa_transactions and bookings tables accordingly.
 * IMPORTANT: This URL must be publicly accessible (HTTPS) for Safaricom to reach it.
 */

require_once '../config/database.php';

// Always respond 200 immediately — Safaricom expects a fast ACK
header('Content-Type: application/json');
http_response_code(200);

// Read and decode the callback body
$raw = file_get_contents('php://input');

// Log raw callback for debugging (optional — disable in production)
$logFile = __DIR__ . '/../logs/mpesa_callback.log';
@mkdir(dirname($logFile), 0755, true);
file_put_contents($logFile, date('Y-m-d H:i:s') . "\n" . $raw . "\n\n", FILE_APPEND);

$data = json_decode($raw, true);

// Acknowledge immediately regardless of content
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);

// Validate structure
if (
    !isset($data['Body']['stkCallback']) ||
    !isset($data['Body']['stkCallback']['MerchantRequestID']) ||
    !isset($data['Body']['stkCallback']['CheckoutRequestID'])
) {
    exit;
}

$callback         = $data['Body']['stkCallback'];
$merchantReqId    = $callback['MerchantRequestID'];
$checkoutReqId    = $callback['CheckoutRequestID'];
$resultCode       = (int) $callback['ResultCode'];
$resultDesc       = $callback['ResultDesc'];

$mpesaReceiptNumber = null;
$transactionDate    = null;
$phoneNumber        = null;
$amount             = null;

// On success (ResultCode = 0), extract metadata
if ($resultCode === 0 && isset($callback['CallbackMetadata']['Item'])) {
    foreach ($callback['CallbackMetadata']['Item'] as $item) {
        switch ($item['Name']) {
            case 'MpesaReceiptNumber': $mpesaReceiptNumber = $item['Value'] ?? null; break;
            case 'TransactionDate':    $transactionDate    = $item['Value'] ?? null; break;
            case 'PhoneNumber':        $phoneNumber        = $item['Value'] ?? null; break;
            case 'Amount':             $amount             = $item['Value'] ?? null; break;
        }
    }
}

try {
    // Update the transaction record
    $stmt = $pdo->prepare("
        UPDATE mpesa_transactions
        SET result_code            = ?,
            result_desc            = ?,
            mpesa_receipt_number   = ?,
            transaction_date       = ?,
            phone_number           = COALESCE(?, phone_number),
            amount                 = COALESCE(?, amount),
            status                 = ?,
            updated_at             = NOW()
        WHERE checkout_request_id  = ?
    ");
    $stmt->execute([
        $resultCode,
        $resultDesc,
        $mpesaReceiptNumber,
        $transactionDate ? date('Y-m-d H:i:s', strtotime($transactionDate)) : null,
        $phoneNumber,
        $amount,
        $resultCode === 0 ? 'completed' : 'failed',
        $checkoutReqId,
    ]);

    // If payment succeeded, update the linked booking
    if ($resultCode === 0) {
        $stmt = $pdo->prepare("
            UPDATE bookings
            SET status         = 'confirmed',
                payment_status = 'paid',
                updated_at     = NOW()
            WHERE id = (
                SELECT booking_id FROM mpesa_transactions
                WHERE checkout_request_id = ?
                LIMIT 1
            )
        ");
        $stmt->execute([$checkoutReqId]);
    }
} catch (PDOException $e) {
    // Log DB error but don't crash — Safaricom doesn't care about our internal errors
    file_put_contents($logFile, 'DB Error: ' . $e->getMessage() . "\n", FILE_APPEND);
}
