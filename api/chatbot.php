<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

$message = strtolower(trim($data['message']));
$sessionId = $data['sessionId'] ?? null;
$timestamp = $data['timestamp'] ?? date('c');

// Predefined responses
$responses = [
    'greetings' => [
        'patterns' => ['hello', 'hi', 'hey', 'greetings', 'good morning', 'good afternoon', 'good evening'],
        'response' => "Hello! 👋 How can I help you plan your perfect trip today?",
        'suggestions' => ['Show me popular destinations', 'How to book a trip?', 'Travel tips']
    ],
    'destinations' => [
        'patterns' => ['popular destinations', 'where to go', 'best places', 'recommend', 'destination', 'visit'],
        'response' => "Explore our top picks for your next adventure!<br><br>🗼 <a href='destinations.html' style='color:inherit;font-weight:600;'>View All Destinations</a><br>🗽 New York City, USA<br>🏺 Santorini, Greece<br>🗻 Tokyo, Japan<br><br>Which one interests you most?",
        'suggestions' => ['Tell me about Paris', 'Show me Tokyo guides', 'Santorini travel tips']
    ],
    'booking' => [
        'patterns' => ['book', 'reservation', 'how to book', 'booking process', 'reserve', 'pay'],
        'response' => "Ready to start your journey? ✈️<br><br>1. Choose your destination<br>2. Select dates<br>3. <a href='booking.html' style='color:inherit;font-weight:600;'>Proceed to Booking</a><br><br>Would you like me to help you start now?",
        'suggestions' => ['Start booking', 'View available dates', 'Payment options']
    ],
    'navigation' => [
        'patterns' => ['navigate', 'where is', 'find', 'page', 'go to', 'show me'],
        'response' => "I can help you find your way around! 🗺️<br><br>🏠 <a href='index.html' style='color:inherit;font-weight:600;'>Home</a><br>🗺️ <a href='destinations.html' style='color:inherit;font-weight:600;'>Destinations</a><br>📝 <a href='signup.html' style='color:inherit;font-weight:600;'>Create Account</a><br>🔑 <a href='login.html' style='color:inherit;font-weight:600;'>Login Page</a><br><br>Where would you like to go?",
        'suggestions' => ['Take me to login', 'Show destinations', 'How to signup?']
    ],
    'tips' => [
        'patterns' => ['tips', 'advice', 'suggestions', 'help', 'guide'],
        'response' => "Check out our <a href='travel-tips.html' style='color:inherit;font-weight:600;'>Exclusive Travel Tips</a>!<br><br>✈️ Book flights early<br>🏨 Check reviews<br>💰 Setting budgets<br><br>Need specific advice?",
        'suggestions' => ['Flight booking tips', 'Accommodation tips', 'Budgeting advice']
    ],
    'contact' => [
        'patterns' => ['contact', 'support', 'help desk', 'phone', 'email'],
        'response' => "Need help? Reach out here: <a href='contact.html' style='color:inherit;font-weight:600;'>Contact Support</a><br><br>📧 Email: support@traveltales.com<br>📞 Phone: +1-888-TRAVEL-TALES<br>💬 Live Chat: 24/7",
        'suggestions' => ['Send email', 'Start live chat', 'View contact hours']
    ]
];

// Find the best matching response
function findResponse($message, $responses) {
    foreach ($responses as $category => $data) {
        foreach ($data['patterns'] as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return [
                    'response' => $data['response'],
                    'suggestions' => $data['suggestions']
                ];
            }
        }
    }
    
    // Default response if no pattern matches
    return [
        'response' => "I'm here to help you plan your perfect trip! You can ask me about:\n\n• Popular destinations\n• Booking process\n• Travel tips\n• Support and contact\n\nWhat would you like to know?",
        'suggestions' => ['Show me destinations', 'How to book?', 'Travel tips', 'Contact support']
    ];
}

// Log the interaction (you might want to store this in a database)
$logEntry = [
    'timestamp' => $timestamp,
    'session_id' => $sessionId,
    'message' => $message,
];

// Get the appropriate response
$responseData = findResponse($message, $responses);

// Send the response
echo json_encode([
    'response' => $responseData['response'],
    'suggestions' => $responseData['suggestions'],
    'timestamp' => date('c')
]);
?> 