<?php
session_start();
include 'db_connect.php';

function getMacAddress() {
    ob_start();
    system('ipconfig /all');
    $content = ob_get_clean();

    // Find active wireless adapter with IPv4 address
    preg_match_all(
        '/Wireless LAN adapter (.*?)(?=Wireless LAN adapter|Ethernet adapter|$)/s', 
        $content, 
        $wirelessAdapters
    );

    foreach ($wirelessAdapters[0] as $adapter) {
        // Check for active connection with IPv4
        if (strpos($adapter, 'IPv4 Address') !== false) {
            preg_match('/Physical Address[ .]+: ([\w-]+)/', $adapter, $macMatch);
            if (!empty($macMatch[1])) {
                $mac = strtoupper(str_replace('-', ':', $macMatch[1]));
                if (strlen($mac) === 17) {
                    return $mac; // Returns 7C:67:A2:37:C3:41
                }
            }
        }
    }

    return '00:00:00:00:00:00'; // Fallback
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $mac_address = getMacAddress();

    // Validate MAC address length and format
    if (strlen($mac_address) !== 17 || !preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/i', $mac_address)) {
        die("<p style='color: red;'>System error: Invalid network configuration</p>");
    }

    // Check if MAC is already online
    $check_mac_query = "SELECT id FROM users WHERE mac_address = ? AND status = 'online'";
    $stmt = $conn->prepare($check_mac_query);
    $stmt->bind_param("s", $mac_address);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<p style='color: red;'>Login denied: This device is already logged in.</p>";
    } else {
        // Check credentials
        $login_query = "SELECT id, name, password_hash, status FROM users WHERE email = ?";
        $stmt = $conn->prepare($login_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] === 'online') {
                echo "<p style='color: red;'>Login denied: Account already in use.</p>";
            } else {
                // Update user status and MAC
                $update_query = "UPDATE users SET status = 'online', mac_address = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("si", $mac_address, $user['id']);
                $stmt->execute();

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                echo "<p style='color: green;'>Login successful! Welcome, " . htmlspecialchars($user['name']) . "</p>";
				
				header("Location: dashboard.php");
				exit;

            }
        } else {
            echo "<p style='color: red;'>Invalid email or password!</p>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tummy Pillow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header>
        <?php include 'header.php'; ?>
    </header>
    <section class="login-section">
        <div class="login-container">
            <h2>Login</h2>
            <form action="login.php" method="POST">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                
                <button type="submit" class="login-button">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Sign up</a></p>
        </div>
    </section>
</div>
</body>
</html>