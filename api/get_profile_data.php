<?php
session_start();
header('Content-Type: application/json');
require_once "../config/database.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION["id"];
$email = $_SESSION["email"];

try {
    // 1. Fetch Boat/Trip Bookings
    $sql_bookings = "SELECT b.id, b.booking_date, b.number_of_people, b.total_price, b.status, b.payment_status, d.name as destination_name, d.image_url 
                     FROM bookings b 
                     JOIN destinations d ON b.destination_id = d.id 
                     WHERE b.user_id = :user_id 
                     ORDER BY b.created_at DESC";
    $stmt_bookings = $pdo->prepare($sql_bookings);
    $stmt_bookings->execute([':user_id' => $user_id]);
    $bookings = $stmt_bookings->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch Flight Bookings (linking via email as the table doesn't have user_id)
    $sql_flights = "SELECT id, destination, date, passengers, class, created_at 
                    FROM flightbook 
                    WHERE email = :email 
                    ORDER BY created_at DESC";
    $stmt_flights = $pdo->prepare($sql_flights);
    $stmt_flights->execute([':email' => $email]);
    $flights = $stmt_flights->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "user" => [
            "username" => $_SESSION["username"],
            "email" => $email
        ],
        "bookings" => $bookings,
        "flights" => $flights
    ]);

} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
