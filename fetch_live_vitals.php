<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'];
// If an admin is viewing a specific patient, use that ID instead
$target_id = $_GET['target_id'] ?? $user_id;

$query = "SELECT * FROM vitals_temp WHERE patient_id = '$target_id' ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

header('Content-Type: application/json');
echo json_json_encode($data);
?>