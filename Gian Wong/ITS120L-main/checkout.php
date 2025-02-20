<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Set status to 'purchased' for all items in the cart
    $query = $conn->query("UPDATE products SET status = 'purchased' WHERE count > 0");

    header("Location: order_confirmation.php"); // Redirect to confirmation page
    exit;
}
?>
