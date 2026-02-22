<?php
/**
 * authenticate.php
 * Handles login via username OR email. Always returns JSON.
 */
session_start();
header('Content-Type: application/json');
require_once "config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

$identifier = trim($_POST["username"] ?? '');
$password   = trim($_POST["password"] ?? '');

if (empty($identifier)) {
    echo json_encode(["login_err" => "Please enter your username or email."]);
    exit;
}
if (empty($password)) {
    echo json_encode(["password_err" => "Please enter your password."]);
    exit;
}

// Try to find user by username OR email
$sql = "SELECT id, username, email, password FROM users WHERE username = :val OR email = :val LIMIT 1";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":val", $identifier, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $row["password"])) {
            $_SESSION["loggedin"] = true;
            $_SESSION["id"]       = $row["id"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["email"]    = $row["email"];
            echo json_encode(["success" => true, "username" => $row["username"]]);
        } else {
            echo json_encode(["login_err" => "Incorrect username/email or password."]);
        }
    } else {
        echo json_encode(["login_err" => "Incorrect username/email or password."]);
    }
} catch (PDOException $e) {
    echo json_encode(["login_err" => "A database error occurred. Please try again."]);
}