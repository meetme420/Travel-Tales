<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    echo json_encode([
        "loggedin" => true,
        "username" => $_SESSION["username"],
        "email" => $_SESSION["email"],
        "id" => $_SESSION["id"]
    ]);
} else {
    echo json_encode(["loggedin" => false]);
}
?>
