<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "db.php";

if (!isset($_SESSION['user_id'])) {
    die("Error: User session not found.");
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['cmd'])) {
    $command = mysqli_real_escape_string($conn, $_POST['cmd']);
    
    // 1. UPDATE ONLY THE CURRENT USER
    $query = "UPDATE users SET scan_command = '$command' WHERE id = '$user_id'";
    
    if(mysqli_query($conn, $query)) {
        // 2. AUTO-RECOVERY CLEANUP: Delete rows for anyone who is not active
        // This ensures vitals_temp stays empty for inactive users
        mysqli_query($conn, "DELETE FROM vitals_temp WHERE patient_id NOT IN (SELECT id FROM users WHERE is_active = 1)");
        
        // 3. Ensure a row exists for the current user so they can receive live data
        $check = mysqli_query($conn, "SELECT patient_id FROM vitals_temp WHERE patient_id = '$user_id'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO vitals_temp (patient_id, temperature, heart_rate, spo2, blood_pressure, respiration) VALUES ('$user_id', 0, 0, 0, '0/0', 0)");
        }

        echo "Success: Command set to " . $command;
    } else {
        echo "SQL Error: " . mysqli_error($conn);
    }
}
?>