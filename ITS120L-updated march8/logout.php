<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Update status to offline in database
    $update_query = "UPDATE users SET status = 'offline' WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

// Destroy session and redirect to login page
session_destroy();
header("Location: login.php");
exit;
?>