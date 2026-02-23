<?php
session_start();
require_once "config/database.php";

$username = $password = $confirm_password = $email = "";
$username_err = $password_err = $confirm_password_err = $email_err = "";
$general_err = ""; // New variable for general errors
$registration_successful = false; // Flag to indicate successful registration

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    $post_username = $_POST["username"] ?? "";
    if (empty(trim($post_username))) {
        $username_err = "Please enter a username.";
    } else {
        $sql = "SELECT id FROM users WHERE username = :username";
        
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $param_username = trim($_POST["username"]);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                $general_err = "Oops! Something went wrong. Please try again later.";
            }
        }
        unset($stmt);
    }
    
    // Validate email
    $post_email = $_POST["email"] ?? "";
    if (empty(trim($post_email))) {
        $email_err = "Please enter an email.";
    } else if (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validate password
    $post_password = $_POST["password"] ?? "";
    if (empty(trim($post_password))) {
        $password_err = "Please enter a password.";     
    } elseif (strlen(trim($post_password)) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($post_password);
    }
    
    // Validate confirm password
    $post_confirm = $_POST["confirm_password"] ?? "";
    if (empty(trim($post_confirm))) {
        $confirm_password_err = "Please confirm password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err) && empty($general_err)) {
        $sql = "INSERT INTO users (username, password, email) VALUES (:username, :password, :email)";
         
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_email = $email;
            
            if ($stmt->execute()) {
                $registration_successful = true;
                
                // Get the last inserted ID
                $last_id = $pdo->lastInsertId();
                
                // Set session variables for auto-login
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $last_id;
                $_SESSION["username"] = $username;
                $_SESSION["email"] = $email;
            } else {
                $general_err = "Something went wrong. Please try again later.";
            }
        }
        unset($stmt);
    }
}

// Prepare the JSON response
$response = array();
if ($registration_successful) {
    $response['success'] = true;
    $response['message'] = "Registration successful!";
} else {
    $response['success'] = false;
    $response['errors'] = array(
        'username_err' => $username_err,
        'email_err' => $email_err,
        'password_err' => $password_err,
        'confirm_password_err' => $confirm_password_err,
        'general_err' => $general_err
    );
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>