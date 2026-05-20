<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Check Role (Is it actually 1?)
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
        die("STOP: Your session role is NOT 1. It is: " . ($_SESSION['role'] ?? 'Empty'));
    }

    // 2. Catch the data
    $vitals_id = mysqli_real_escape_string($conn, $_POST['vitals_id']);
    $note_text = mysqli_real_escape_string($conn, $_POST['note_text']);
    $redirect_id = mysqli_real_escape_string($conn, $_POST['redirect_id']);

    // 3. Check if ID is empty
    if (empty($vitals_id)) {
        die("STOP: The Vitals ID was not sent from the dashboard form.");
    }

    // 4. Run the update
    $query = "UPDATE vitals_history SET notes = '$note_text' WHERE id = '$vitals_id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: dashboard.php?view_user_id=" . $redirect_id);
        exit();
    } else {
        die("DATABASE ERROR: " . mysqli_error($conn));
    }
} else {
    die("STOP: You didn't submit the form correctly.");
}
?>