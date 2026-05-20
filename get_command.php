<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Capture the ID from the ESP32 URL (e.g., get_command.php?id=11)
$patient_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 1; 

// Use the dynamic $patient_id variable instead of a hardcoded 1
$query = "SELECT scan_command FROM users WHERE id = '$patient_id' LIMIT 1"; 
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

$row = mysqli_fetch_assoc($result);
echo $row['scan_command'] ?? 'IDLE';
?>