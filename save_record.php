<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'GET') {
    $patient_id = isset($_REQUEST['id']) ? mysqli_real_escape_string($conn, $_REQUEST['id']) : null;

    if (!$patient_id) {
        die("Error: Patient ID is missing.");
    }

    // 1. Fetch current live data
    $live_data_query = "SELECT * FROM vitals_temp WHERE patient_id = '$patient_id' LIMIT 1";
    $result = mysqli_query($conn, $live_data_query);

    if ($row = mysqli_fetch_assoc($result)) {
        $temp = $row['temperature'];
        $hr   = $row['heart_rate'];
        $spo2 = $row['spo2'];
        $bp   = $row['blood_pressure'];
        $resp = $row['respiration'];

        // 2. SAVE INTO HISTORY TABLE
        $history_query = "INSERT INTO vitals_history (patient_id, temperature, heart_rate, spo2, blood_pressure, respiration, created_at) 
                          VALUES ('$patient_id', '$temp', '$hr', '$spo2', '$bp', '$resp', NOW())";

        if (mysqli_query($conn, $history_query)) {
            
            // --- NEW: RESET LOGIC ---
            // This clears the live dashboard values back to zero/default
            $reset_query = "UPDATE vitals_temp SET 
                            temperature = 0, 
                            heart_rate = 0, 
                            spo2 = 0, 
                            blood_pressure = '0/0', 
                            respiration = 0 
                            WHERE patient_id = '$patient_id'";
            
            mysqli_query($conn, $reset_query);
            // ------------------------

            // SUCCESS: Redirect back to dashboard
            header("Location: dashboard.php?view_user_id=$patient_id&success=1");
            exit;
        } else {
            echo "Database Error (History): " . mysqli_error($conn);
        }
    } else {
        echo "No live data found. Please perform a scan first.";
    }
}
?>