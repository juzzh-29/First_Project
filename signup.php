<?php
include 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']); 

    // Updated SQL: Set role to 0 and scan_command to 'IDLE' by default
    $sql = "INSERT INTO users (fullname, email, password, role, scan_command) 
            VALUES ('$fullname', '$email', '$password', 0, 'IDLE')";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>