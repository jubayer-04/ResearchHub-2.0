<?php
$servername = "sql103.infinityfree.com";
$username   = "if0_40665526";
$password   = "fb30O4XqTB1CB";
$database   = "if0_40665526_researchhub";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
