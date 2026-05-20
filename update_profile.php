<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Check if session exists
    if (!isset($_SESSION['user_id'])) {
        echo "Unauthorized access";
        exit();
    }

    $uid = $_SESSION['user_id'];
    
    // 2. Sanitize all inputs to prevent SQL Injection
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $sex = mysqli_real_escape_string($conn, $_POST['sex']);
    $weight = mysqli_real_escape_string($conn, $_POST['weight']);
    $height = mysqli_real_escape_string($conn, $_POST['height']);
    $address = mysqli_real_escape_string($conn, $_POST['address']); // Grab the address!

    // 3. Update the SQL to include the address column
    $sql = "UPDATE users SET 
            age='$age', 
            sex='$sex', 
            weight='$weight', 
            height='$height', 
            address='$address' 
            WHERE id='$uid'";

    if (mysqli_query($conn, $sql)) {
        // Also update the session fullname if you changed it in the form
        if (isset($_POST['fullname'])) {
            $fname = mysqli_real_escape_string($conn, $_POST['fullname']);
            mysqli_query($conn, "UPDATE users SET fullname='$fname' WHERE id='$uid'");
            $_SESSION['fullname'] = $fname;
        }
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>