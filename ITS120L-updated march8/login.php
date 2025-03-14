<?php 
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Regex validation for email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?error=Invalid+email+format");
        exit();
    }

    // Regex validation for password (same as register.php)
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        header("Location: login.php?error=Invalid+password+format");
        exit();
    }

    // Check credentials
    $login_query = "SELECT id, name, password_hash, status FROM users WHERE email = ?";
    $stmt = $conn->prepare($login_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        if ($user['status'] === 'online') {
            header("Location: login.php?error=Account+already+in+use");
            exit();
        } else {
            // Update user status
            $update_query = "UPDATE users SET status = 'online' WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            
            header("Location: menu.php");
            exit();
        }
    } else {
        header("Location: login.php?error=Invalid+email+or+password");
        exit();
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
    <script>
        function validateLoginForm() {
            let email = document.getElementById("email").value.trim();
            let password = document.getElementById("password").value.trim();

            let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert("Invalid email format!");
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
    <section class="login-section">
        <div class="login-container">
            <h2>Login</h2>

            <?php if (isset($_GET['error'])) { echo "<p style='color: red;'>" . htmlspecialchars($_GET['error']) . "</p>"; } ?>

            <form action="login.php" method="POST" onsubmit="return validateLoginForm();">
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
