<?php
session_start();
include 'db.php'; 

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // 1. CLEAR THE USER TABLE: Set to inactive and stop commands
    $resetUser = "UPDATE users SET is_active = 0, scan_command = 'IDLE' WHERE id = '$user_id'";
    mysqli_query($conn, $resetUser);

    // 2. VANISH: Completely remove the row from vitals_temp so the table is empty
    $deleteVitals = "DELETE FROM vitals_temp WHERE patient_id = '$user_id'";
    mysqli_query($conn, $deleteVitals);
}

// 3. SECURE SESSION DESTRUCTION
$_SESSION = array(); 
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], $params["domain"], 
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// 4. BROWSER CACHE PREVENTION
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

header("Location: index.html");
exit();
?>