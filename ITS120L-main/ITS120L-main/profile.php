<?php
function getMacAddress() {
    ob_start();
    system('ipconfig /all');
    $content = ob_get_clean();

    preg_match_all(
        '/Wireless LAN adapter (.*?)(?=Wireless LAN adapter|Ethernet adapter|$)/s', 
        $content, 
        $wirelessAdapters
    );

    foreach ($wirelessAdapters[0] as $adapter) {
        if (strpos($adapter, 'IPv4 Address') !== false) {
            preg_match('/Physical Address[ .]+: ([\w-]+)/', $adapter, $macMatch);
            if (!empty($macMatch[1])) {
                $mac = strtoupper(str_replace('-', ':', $macMatch[1]));
                if (strlen($mac) === 17) {
                    return $mac;
                }
            }
        }
    }

    return '00:00:00:00:00:00';
}

$macAddress = getMacAddress();

$conn = new mysqli('localhost', 'root', '123456', 'tummy_pillow_db');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Change the SQL query to select all users with the same MAC address
$sql = "SELECT name, email, phone_number, created_at, status FROM users WHERE mac_address = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $macAddress);
$stmt->execute();
$result = $stmt->get_result();

$userFound = false; // Flag to check if an online user is found
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tummy Pillow</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Simple notification style */
        .notification {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 16px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 9999;
            display: none;
        }
        .notification.error {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <?php include 'header.php'; ?>
        </header>

        <!-- Notification area -->
        <div id="notification" class="notification"></div>

        <?php
        // Loop through the results to find an online user
        while ($user = $result->fetch_assoc()) {
            if ($user['status'] === 'online') {
                $userFound = true; // Set the flag to true
                echo '<section class="profile-section" style="display: flex; justify-content: center; align-items: center; height: auto;">
                <div class="profile-container" style="text-align: center;">
                    <h2>Your Profile</h2>
                    <div class="profile-info">
                        <p><strong>Name:</strong> ' . htmlspecialchars($user['name']) . '</p>
                        <p><strong>Email:</strong> ' . htmlspecialchars($user['email']) . '</p>
                        <p><strong>Number:</strong> ' . htmlspecialchars($user['phone_number']) . '</p>
                        <p><strong>Joined:</strong> ' . htmlspecialchars($user['created_at']) . '</p>
                    </div>
                    
                    <h3>Edit Profile</h3>
                    <form method="post" action="">
                        <select name="field_to_update" required>
                            <option value="">Select field to update</option>
                            <option value="name">Name</option>
                            <option value="email">Email</option>
                            <option value="phone_number">Phone Number</option>
                        </select>
                        <input type="text" name="new_value" placeholder="Enter new value" required>
                        <button type="submit" name="update">Update Profile</button>
                    </form>
                    
                    <form method="post" action="">
                        <button type="submit" name="logout">Logout</button>
                    </form>
                </div>';

                // Handle update and logout requests
                if (isset($_POST['update'])) {
                    $field = $_POST['field_to_update'];
                    $newValue = $_POST['new_value'];

                                        if (in_array($field, ['name', 'email', 'phone_number'])) {
                        $updateSql = "UPDATE users SET $field = ? WHERE mac_address = ? AND status = 'online'";
                        $updateStmt = $conn->prepare($updateSql);
                        if (!$updateStmt) {
                            die('Prepare failed: ' . $conn->error);
                        }
                        $updateStmt->bind_param('ss', $newValue, $macAddress);
                        $updateStmt->execute();

                        if ($updateStmt->affected_rows > 0) {
                            echo "<script>document.getElementById('notification').innerText = '" . ucfirst($field) . " updated successfully!'; document.getElementById('notification').style.display = 'block';</script>";
                        } else {
                            echo "<script>document.getElementById('notification').innerText = 'Failed to update. Make sure you are online.'; document.getElementById('notification').style.display = 'block';</script>";
                        }
                        $updateStmt->close();
                    } else {
                        echo "<script>document.getElementById('notification').innerText = 'Invalid field selection.'; document.getElementById('notification').style.display = 'block';</script>";
                    }
                }

                if (isset($_POST['logout'])) {
                    $logoutSql = "UPDATE users SET status = 'offline' WHERE mac_address = ?";
                    $logoutStmt = $conn->prepare($logoutSql);
                    if (!$logoutStmt) {
                        die('Prepare failed: ' . $conn->error);
                    }
                    $logoutStmt->bind_param('s', $macAddress);
                    $logoutStmt->execute();
                    echo "<script>document.getElementById('notification').innerText = 'You have been logged out.'; document.getElementById('notification').style.display = 'block';</script>";
                    $logoutStmt->close();
                }

                echo '</section>'; // Close the profile section
                break; // Exit the loop since we found an online user
            }
        }

        if (!$userFound) {
            echo "<script>document.getElementById('notification').innerText = 'No online user found with this MAC address.'; document.getElementById('notification').style.display = 'block';</script>";
        }

        $stmt->close();
        $conn->close();
        ?>
    </div>

    <script>
        // Hide notification after a few seconds
        setTimeout(function() {
            document.getElementById('notification').style.display = 'none';
        }, 5000);
    </script>
</body>
</html>