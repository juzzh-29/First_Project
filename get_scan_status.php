<?php
include "db.php";

// 1. Find the user who is currently logged in (Active)
$query = "SELECT id, scan_command FROM users WHERE is_active = 1 LIMIT 1"; 
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    $command = trim($row['scan_command']);
    $user_id = $row['id'];
    
    // If the command is empty or null, make sure it says IDLE
    if (empty($command)) {
        $command = "IDLE";
    }

    // Send COMMAND:ID (e.g., SCAN_TEMP:11 or IDLE:11)
    // This tells the ESP32 "Stay idle, but you are watching Patient 11"
    echo $command . ":" . $user_id; 

} else {
    // If no one is logged in at all, then we go to 0
    echo "IDLE:0";
}
?>