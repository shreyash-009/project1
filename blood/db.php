<?php
$host = "localhost";
$user = "root"; // XAMPP default
$pass = "";     // XAMPP default
$db = "blood_donor";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
