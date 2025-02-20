<?php
$host = "localhost";
$user = "root";
$password = "123456";
$dbname = "tummy_pillow_db"; // Replace with your actual database name

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
