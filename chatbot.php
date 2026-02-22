<?php
header("Content-Type: application/json");

// Predefined chatbot responses
$responses = [
    "flight booking" => "We can help you book flights at competitive rates. Please visit our flight booking page.",
    "housing" => "We provide housing arrangements including hotels and rentals. Let us know your preferences.",
    "safety" => "Your safety is our priority! We offer travel insurance and real-time support during your journey."
];

// Get user input from AJAX request (Handle JSON or POST)
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$userInput = strtolower($data["message"] ?? $_POST["userInput"] ?? "");

// Check for related queries
if (strpos($userInput, "flight") !== false) {
    $reply = $responses["flight booking"];
} elseif (strpos($userInput, "housing") !== false) {
    $reply = $responses["housing"];
} elseif (strpos($userInput, "safety") !== false) {
    $reply = $responses["safety"];
} elseif ($userInput === "exit") {
    $reply = "Thank you for chatting with us! Safe travels!";
} else {
    $reply = "I'm sorry, I don't understand. Can you rephrase your question?";
}

// Return response in JSON format
echo json_encode(["response" => $reply]);
?>
