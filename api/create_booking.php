<?php
session_start();
require_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["error" => "Please login to make a booking"]);
    exit();
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Validate input
    if (!isset($data["destination_id"]) || !isset($data["booking_date"]) || !isset($data["number_of_people"])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit();
    }
    
    try {
        // Get destination price
        $stmt = $pdo->prepare("SELECT price FROM destinations WHERE id = ?");
        $stmt->execute([$data["destination_id"]]);
        $destination = $stmt->fetch();
        
        if (!$destination) {
            echo json_encode(["error" => "Destination not found"]);
            exit();
        }
        
        // Calculate total price
        $total_price = $destination["price"] * $data["number_of_people"];
        
        // Create booking (payment_status defaults to 'unpaid' until M-Pesa confirms)
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, destination_id, booking_date, number_of_people, total_price, payment_status) VALUES (?, ?, ?, ?, ?, 'unpaid')");
        $stmt->execute([
            $_SESSION["id"],
            $data["destination_id"],
            $data["booking_date"],
            $data["number_of_people"],
            $total_price
        ]);
        
        // Get booking details
        $booking_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("
            SELECT b.*, d.name as destination_name 
            FROM bookings b 
            JOIN destinations d ON b.destination_id = d.id 
            WHERE b.id = ?
        ");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            "success"    => true,
            "message"    => "Booking created successfully",
            "booking_id" => (int) $booking_id,
            "booking"    => $booking
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?> 