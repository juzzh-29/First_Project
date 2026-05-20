<?php
$conn = mysqli_connect("localhost", "root", "", "Vitalsv2.0_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>