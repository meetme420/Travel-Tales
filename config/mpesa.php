<?php
// =============================================
// M-Pesa Daraja API Configuration
// =============================================
// Register at: https://developer.safaricom.co.ke
// Use sandbox credentials for testing, production for live.

// Environment: 'sandbox' or 'production'
define('MPESA_ENV', 'sandbox');

// Daraja API credentials (from your Safaricom developer portal app)
define('MPESA_CONSUMER_KEY',    'UZB6OM0luNv9GxBS7xqo8hAqJje26M8fe8ALDzrkhGtUIOdv');
define('MPESA_CONSUMER_SECRET', 'jDCdx2kBfjrKi6CWWmXSr7rqP5bBgVTaXsTRj7vp0LHo4dfQUrrirOsBGb1kcBwR');

// Lipa Na M-Pesa shortcode (use 174379 for sandbox STK Push)
define('MPESA_SHORTCODE', '174379');

// Lipa Na M-Pesa Online Passkey (from your Safaricom developer portal)
define('MPESA_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');

// Transaction type: 'CustomerPayBillOnline' (Paybill) or 'CustomerBuyGoodsOnline' (Till)
define('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline');

// Callback URL — must be a public HTTPS URL reachable by Safaricom.
// For local testing, expose your server with ngrok: ngrok http 80
// Then set this to: https://YOUR_NGROK_ID.ngrok.io/api/mpesa_callback.php
define('MPESA_CALLBACK_URL', 'https://spinier-supersolemnly-loni.ngrok-free.dev/api/mpesa_callback.php');

// Currency conversion: 1 USD → KES
// Update this as exchange rate changes, or connect to a live FX API.
define('USD_TO_KES', 130);

// Daraja API base URLs
define('MPESA_API_BASE', MPESA_ENV === 'production'
    ? 'https://api.safaricom.co.ke'
    : 'https://sandbox.safaricom.co.ke'
);
