<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"], $_POST["id"])) {
    $id = (int) $_POST["id"];
    $action = $_POST["action"];

    // Fetch current count
    $query = $conn->prepare("SELECT count FROM products WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $count = (int) $row["count"];

        // Increase or decrease quantity
        if ($action === "increase") {
            $count++;
        } elseif ($action === "decrease" && $count > 1) {
            $count--;
        } elseif ($action === "decrease" && $count == 1) {
            $count = 0; // Set count to 0 if decreasing from 1
        }

        // Update status based on count
        $status = ($count > 0) ? 'on cart' : 'none';

        // Update the database
        $updateQuery = $conn->prepare("UPDATE products SET count = ?, status = ? WHERE id = ?");
        $updateQuery->bind_param("isi", $count, $status, $id);
        $updateQuery->execute();
    }
}
?>
