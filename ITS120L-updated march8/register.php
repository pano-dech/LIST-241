<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Tummy Pillow</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function validateForm() {
            let name = document.getElementById("name").value.trim();
            let phone = document.getElementById("number").value.trim();
            let address = document.getElementById("address").value.trim();
            let email = document.getElementById("email").value.trim();
            let password = document.getElementById("password").value.trim();

            let nameRegex = /^[A-Z][a-zA-Z\s'-]{1,49}$/;
            if (!nameRegex.test(name)) {
                alert("Invalid name! Use only letters, spaces, hyphens (-), and apostrophes ('), and start with an uppercase letter.");
                return false;
            }

            let phoneRegex = /^09\d{9}$/;
            if (!phoneRegex.test(phone)) {
                alert("Invalid phone number! It must be 11 digits and start with '09'. Example: 09228912561.");
                return false;
            }

            let addressRegex = /^[A-Za-z0-9\s.,#-]{5,100}$/;
            if (!addressRegex.test(address)) {
                alert("Invalid address! It must be at least 5 characters long.");
                return false;
            }

            let passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;
            if (!passwordRegex.test(password)) {
                alert("Invalid password! Must be at least 8 characters long and contain at least 1 letter and 1 number.");
                return false;
            }
            return true;
        }
    </script>
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

                    function getMacAddress() {
                        $os = PHP_OS;
                        if (strtoupper(substr($os, 0, 3)) === 'WIN') {
                            $output = shell_exec("getmac");
                        } else {
                            $output = shell_exec("ip link show");
                        }
                        if (preg_match('/([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})/', $output, $matches)) {
                            return $matches[0];
                        }
                        return 'UNKNOWN';
                    }

                    $name = htmlspecialchars($_POST['name']);
                    $number = htmlspecialchars($_POST['number']);
                    $email = htmlspecialchars($_POST['email']);
                    $address = htmlspecialchars($_POST['address']);
                    $password = $_POST['password'];
                    $status = 'offline';
                    $mac_address = getMacAddress();

                    if (!preg_match('/^[A-Z][a-zA-Z\s\'-]{1,49}$/', $name)) {
                        header("Location: register.php?error=Invalid+name+format");
                        exit();
                    }
                    if (!preg_match('/^09\d{9}$/', $number)) {
                        header("Location: register.php?error=Invalid+phone+number+format");
                        exit();
                    }
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        header("Location: register.php?error=Invalid+email+format");
                        exit();
                    }
                    if (!preg_match('/^[A-Za-z0-9\s.,#-]{5,100}$/', $address)) {
                        header("Location: register.php?error=Invalid+address+format");
                        exit();
                    }
                    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
                        header("Location: register.php?error=Invalid+password+format");
                        exit();
                    }
                    
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    $check_query = "SELECT id FROM users WHERE email = ?";
                    $stmt = $conn->prepare($check_query);
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->store_result();
                    
                    if ($stmt->num_rows > 0) {
                        header("Location: register.php?error=Email+already+registered");
                        exit();
                    } else {
                        $stmt = $conn->prepare("INSERT INTO users (name, phone_number, email, address, password_hash, mac_address, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                        $stmt->bind_param("sssssss", $name, $number, $email, $address, $password_hash, $mac_address, $status);
                        if ($stmt->execute()) {
                            header("Location: register.php?success=Registration+successful");
                            exit();
                        } else {
                            header("Location: register.php?error=Error+in+registration");
                            exit();
                        }
                    }
                    $stmt->close();
                    $conn->close();
                }
                ?>

                <?php if (isset($_GET['error'])) { echo "<p style='color: red;'>" . htmlspecialchars($_GET['error']) . "</p>"; } ?>
                <?php if (isset($_GET['success'])) { echo "<p style='color: green;'>" . htmlspecialchars($_GET['success']) . "</p>"; } ?>

                <form action="register.php" method="POST" onsubmit="return validateForm();">
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