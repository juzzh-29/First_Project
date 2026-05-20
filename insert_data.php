<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture patient_id from ESP32
    $patient_id = isset($_POST['patient_id']) ? mysqli_real_escape_string($conn, $_POST['patient_id']) : null; 
    $type = $_POST['type'] ?? '';

    if (!$patient_id) {
        die("Error: No patient_id provided.");
    }

    // --- AUTO-CREATE TEMP ROW IF MISSING (Only if user exists) ---
    $check_exists = mysqli_query($conn, "SELECT patient_id FROM vitals_temp WHERE patient_id = '$patient_id'");
    if (mysqli_num_rows($check_exists) == 0) {
        mysqli_query($conn, "INSERT INTO vitals_temp (patient_id, temperature, heart_rate, spo2, blood_pressure, respiration) VALUES ('$patient_id', 0, 0, 0, '0/0', 0)");
    }

    // Reset command to IDLE
    mysqli_query($conn, "UPDATE users SET scan_command = 'IDLE' WHERE id = '$patient_id'");

    $history_query = "";
    $live_query = "";

    if ($type == "TEMP") {
        $val = mysqli_real_escape_string($conn, $_POST['temperature']);
        $history_query = "INSERT INTO vitals (patient_id, temperature, created_at) VALUES ('$patient_id', '$val', NOW())";
        $live_query = "UPDATE vitals_temp SET temperature = '$val' WHERE patient_id = '$patient_id'";
    } 
    elseif ($type == "HR") {
        $hr = mysqli_real_escape_string($conn, $_POST['heart_rate']);
        $spo2 = mysqli_real_escape_string($conn, $_POST['spo2']);
        $history_query = "INSERT INTO vitals (patient_id, heart_rate, spo2, created_at) VALUES ('$patient_id', '$hr', '$spo2', NOW())";
        $live_query = "UPDATE vitals_temp SET heart_rate = '$hr', spo2 = '$spo2' WHERE patient_id = '$patient_id'";
    }
    elseif ($type == "BP") {
        $bp = mysqli_real_escape_string($conn, $_POST['blood_pressure']);
        $history_query = "INSERT INTO vitals (patient_id, blood_pressure, created_at) VALUES ('$patient_id', '$bp', NOW())";
        $live_query = "UPDATE vitals_temp SET blood_pressure = '$bp' WHERE patient_id = '$patient_id'";
    }
    elseif ($type == "RESP") {
        $resp = mysqli_real_escape_string($conn, $_POST['respiration']);
        $history_query = "INSERT INTO vitals (patient_id, respiration, created_at) VALUES ('$patient_id', '$resp', NOW())";
        $live_query = "UPDATE vitals_temp SET respiration = '$resp' WHERE patient_id = '$patient_id'";
    }

    if (!empty($history_query)) {
        mysqli_query($conn, $live_query);
        if (mysqli_query($conn, $history_query)) {
            echo "Success: User $patient_id";
        }
    }
}
?>