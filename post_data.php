<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from ESP32. Fallback to 1 for testing if patient_id is missing.
    $patient_id = $_POST['patient_id'] ?? 1; 
    $type = $_POST['type'] ?? '';
    
    // Initialize query variables
    $history_query = "";
    $live_query = "";

    // Clear existing live data for this specific scan type to keep it "Live"
    mysqli_query($conn, "DELETE FROM vitals_temp WHERE patient_id = '$patient_id'");

    if ($type == "TEMP") {
        $val = mysqli_real_escape_string($conn, $_POST['temperature']);
        $history_query = "INSERT INTO vitals_history (patient_id, temperature) VALUES ('$patient_id', '$val')";
        $live_query = "INSERT INTO vitals_temp (patient_id, temperature) VALUES ('$patient_id', '$val')";
    } 
    elseif ($type == "HR") {
        $hr = mysqli_real_escape_string($conn, $_POST['heart_rate']);
        $spo2 = mysqli_real_escape_string($conn, $_POST['spo2']);
        $history_query = "INSERT INTO vitals_history (patient_id, heart_rate, spo2) VALUES ('$patient_id', '$hr', '$spo2')";
        $live_query = "INSERT INTO vitals_temp (patient_id, heart_rate, spo2) VALUES ('$patient_id', '$hr', '$spo2')";
    }
    elseif ($type == "BP") {
        $bp = mysqli_real_escape_string($conn, $_POST['blood_pressure']);
        $history_query = "INSERT INTO vitals_history (patient_id, blood_pressure) VALUES ('$patient_id', '$bp')";
        $live_query = "INSERT INTO vitals_temp (patient_id, blood_pressure) VALUES ('$patient_id', '$bp')";
    }
    elseif ($type == "RESP") {
        $resp = mysqli_real_escape_string($conn, $_POST['respiration']);
        $history_query = "INSERT INTO vitals_history (patient_id, respiration) VALUES ('$patient_id', '$resp')";
        $live_query = "INSERT INTO vitals_temp (patient_id, respiration) VALUES ('$patient_id', '$resp')";
    }

    // Execute the updates
    if (!empty($live_query)) {
        mysqli_query($conn, $live_query); // Show on Dashboard
        mysqli_query($conn, $history_query); // Save to History
        
        // Reset command to IDLE so the ESP32 stops scanning
        mysqli_query($conn, "UPDATE users SET scan_command = 'IDLE' WHERE id = '$patient_id'");
        
        echo "Success: Live and History updated.";
    } else {
        echo "Error: Unknown scan type.";
    }
}
?>