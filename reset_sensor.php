<?php
include 'db.php';
session_start();

if (!isset($_GET['type']) || !isset($_GET['id'])) {
    die("Missing parameters");
}

$type = mysqli_real_escape_string($conn, $_GET['type']);
$id = mysqli_real_escape_string($conn, $_GET['id']);

switch ($type) {
    case 'temp':
        mysqli_query($conn, "UPDATE vitals_temp SET temperature = 0 WHERE patient_id = '$id'");
        break;
    case 'hr':
        mysqli_query($conn, "UPDATE vitals_temp SET heart_rate = 0, spo2 = 0 WHERE patient_id = '$id'");
        break;
    case 'bp':
        mysqli_query($conn, "UPDATE vitals_temp SET blood_pressure = '0/0' WHERE patient_id = '$id'");
        break;
    case 'resp':
        mysqli_query($conn, "UPDATE vitals_temp SET respiration = 0 WHERE patient_id = '$id'");
        break;
    case 'all':
        mysqli_query($conn, "UPDATE vitals_temp SET temperature=0, heart_rate=0, spo2=0, blood_pressure='0/0', respiration=0 WHERE patient_id = '$id'");
        break;
}

echo "Success";
?>