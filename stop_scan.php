<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Get the ID of the patient being reset
$target_id = isset($_GET['view_user_id']) ? mysqli_real_escape_string($conn, $_GET['view_user_id']) : $_SESSION['user_id'];

// 1. Wipe the temporary live data so the dashboard shows "NO DATA"
mysqli_query($conn, "DELETE FROM vitals_temp WHERE patient_id = '$target_id'");

// 2. Tell the hardware to stop scanning and wait
mysqli_query($conn, "UPDATE users SET scan_command = 'IDLE', is_active = 0 WHERE id = '$target_id'");

// 3. Send them back to the dashboard
header("Location: dashboard.php?view_user_id=" . $target_id);
exit();
?>