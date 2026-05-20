<?php
include "db.php";
header('Content-Type: application/json');

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;

// Fetch from your temp table since that's where the ESP32 sends live data
$query = "SELECT * FROM vitals_temp WHERE patient_id = '$id' LIMIT 1";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    // We send this back to the dashboard.js
    echo json_encode([
        'status' => 'success',
        'data' => [
            'temperature' => $row['temperature'],
            'heart_rate' => $row['heart_rate'],
            'spo2' => $row['spo2'],
            'blood_pressure' => $row['blood_pressure'],
            'respiration' => $row['respiration']
        ]
    ]);
} else {
    echo json_encode(['status' => 'error']);
}
?>