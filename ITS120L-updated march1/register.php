<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Tummy Pillow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <?php include 'header.php'; ?>
        </header>
        
        <section class="register-section">
            <div class="register-container">
                <h2>Register</h2>

                 <?php
                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    include 'db_connect.php'; 

                    // Enhanced MAC address retrieval
                    function getMacAddress() {
                        ob_start();
                        system('ipconfig /all');
                        $content = ob_get_clean();
                        
                        // Find active wireless adapter with IPv4
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
                        return '00:00:00:00:00:00'; // Fallback
                    }

                    $name = htmlspecialchars($_POST['name']);
                    $number = htmlspecialchars($_POST['number']);
                    $email = htmlspecialchars($_POST['email']);
                    $address = htmlspecialchars($_POST['address']);
                    $password = $_POST['password'];
                    $status = 'offline';
                    $mac_address = getMacAddress();

                    // Validate MAC address format
                    if (!preg_match('/^([0-9A-F]{2}[:]){5}([0-9A-F]{2})$/', $mac_address)) {
                        die("<p style='color: red;'>System error: Invalid network configuration</p>");
                    }

                    // Hash the password
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    // Check for existing email or online MAC address
                    $check_query = "SELECT id FROM users WHERE email = ? OR (mac_address = ? AND status = 'online')";
                    $stmt = $conn->prepare($check_query);
                    $stmt->bind_param("ss", $email, $mac_address);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        echo "<p style='color: red;'>Email already registered or device is currently in use!</p>";
                    } else {
                        // Insert new user
                        $stmt = $conn->prepare("INSERT INTO users (name, phone_number, email, address, mac_address, password_hash, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                        $stmt->bind_param("sssssss", $name, $number, $email, $address, $mac_address, $password_hash, $status);

                        if ($stmt->execute()) {
                            echo "<p style='color: green;'>Registration successful!</p>";
                        } else {
                            echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
                        }
                    }
                    $stmt->close();
                    $conn->close();
                }
                ?>

                <form action="register.php" method="POST">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                    
                    <label for="number">Phone number:</label>
                    <input type="text" id="number" name="number" required>
                    
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" required>
                    
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    
                    <button type="submit" class="register-button">Register</button>
                </form>
                
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </section>
    </div>
</body>
</html>