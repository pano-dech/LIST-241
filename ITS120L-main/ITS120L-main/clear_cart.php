<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Reset cart items: set count = 0 and status = none
    $query = $conn->query("UPDATE products SET count = 0, status = 'none' WHERE count > 0");

    header("Location: cart.php"); // Redirect back to cart page
    exit;
}
?>
