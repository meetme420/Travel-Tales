<?php
include 'config/database.php';
// Use $pdo from database.php instead of $conn
if(isset($_POST['submit'])){
    $destination = $_POST['destination'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $date = $_POST['date'];
    $passengers = $_POST['passengers'];
    $class = $_POST['class'];
    try {
        $sql = "INSERT INTO flightbook (destination, fullname, email, date, passengers, class) 
                VALUES (:destination, :fullname, :email, :date, :passengers, :class)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':destination' => $destination,
            ':fullname' => $fullname,
            ':email' => $email,
            ':date' => $date,
            ':passengers' => $passengers,
            ':class' => $class
        ]);
        $success = true;
    } catch (PDOException $e) {
        $success = false;
    }

    if($success){
        include 'flight.html';
        echo "<script>alert('Bookings Done! Thank You')</script>";
        
    }else{
        include 'flight.html';
        echo "<script>alert('Data not inserted')</script>";
        
    }


}
?>
