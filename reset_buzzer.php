<?php
include 'db.php';
$user_id = $_GET['id'];
mysqli_query($conn, "UPDATE users SET buzzer_status = 0 WHERE id = '$user_id'");
?>