<?php
session_start();
$conn = new mysqli("localhost", "root", "123456", "tummy_pillow_db");

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert predefined products if they do not exist
$products = [
    [1, 'Hot Deals', 'Garlic Cream Cheese Buns - Box of 6 Regular Size Buns', 'gccb1.jpg', 'Garlic Cream Cheese Buns Deal', 420.00, 'box of 6'],
    [2, 'Hot Deals', 'Garlic Cream Cheese Buns - Box of 4 Big Size Buns', 'gccb1.jpg', 'Garlic Cream Cheese Buns Deal', 420.00, 'box of 4'],
    [3, 'Hot Deals', 'Chocolate Revel Bars - Box of 16 Bars', 'crb2.jpg', 'Chocolate Revel Bars Deal', 440.00, 'box of 16'],
    [4, 'Menu', 'Cinnamon Rolls - Box of 4', 'cr3.jpg', 'Cinnamon Rolls', 420.00, 'box of 4'],
    [5, 'Menu', 'Empanadas - Box of 4', 'empa1.jpg', 'Empanadas', 260.00, 'box of 4'],
    [6, 'Menu', 'Empanadas - Box of 12', 'empa1.jpg', 'Empanadas', 780.00, 'box of 12']
];

$stmt = $conn->prepare("INSERT INTO products (id, category, name, image_url, description, price, quantity, status, count)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'none', 0)
    ON DUPLICATE KEY UPDATE 
    category=VALUES(category), name=VALUES(name), image_url=VALUES(image_url), 
    description=VALUES(description), price=VALUES(price), quantity=VALUES(quantity)");

foreach ($products as $product) {
    $stmt->bind_param("issssds", ...$product);
    $stmt->execute();
}
$stmt->close();

// Handle Add to Cart request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["product_id"])) {
    $product_id = (int) $_POST["product_id"];

    // Securely fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if ($product) {
        $item = [
            "id" => $product["id"],
            "name" => $product["name"],
            "price" => $product["price"],
            "image_url" => $product["image_url"],
            "quantity" => 1
        ];

        // Initialize cart session
        if (!isset($_SESSION["cart"])) {
            $_SESSION["cart"] = [];
        }

        // Check if item is already in cart
        $found = false;
        foreach ($_SESSION["cart"] as &$cart_item) {
            if ($cart_item["id"] == $product_id) {
                $cart_item["quantity"]++;
                $found = true;
                break;
            }
        }

        // If item not found, add to cart
        if (!$found) {
            $_SESSION["cart"][] = $item;
        }

        // Update database: Append "on cart" or increment count
        $update_stmt = $conn->prepare("UPDATE products SET 
            status = CONCAT(IF(status = 'none', '', CONCAT(status, ',')), 'on cart'), 
            count = count + 1 
            WHERE id = ?");
        $update_stmt->bind_param("i", $product_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
}

// Count total cart items
$count = isset($_SESSION["cart"]) ? array_sum(array_column($_SESSION["cart"], "quantity")) : 0;

// Fetch products from database
$result = $conn->query("SELECT * FROM products");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tummy Pillow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo-container">
                <a href="index.php">
                    <img src="images/logo.png" alt="Tummy Pillow Logo">
                </a>
            </div>
            <nav>
                <a href="who-we-are.php">Who We Are</a>
                <a href="menu.php" style="color: orange; font-weight: bold;">Menu</a>
                <a href="profile.html">Profile</a>
                <a href="cart.php">
                    <button class="cart-button">Cart (<?= $count ?>)</button>
                </a>
            </nav>
        </header>

        <section class="hero">
            <img src="images/gccb2.jpg" alt="Bread and Flowers">
        </section>
        <section class="welcome-message">
            <h2>Welcome to Tummy Pillow, where there's comfort in every bite!</h2>
        </section>

        <section class="hot-deals">
            <h2>Hot Deals!</h2>
            <?php 
                $hot_deals = $conn->query("SELECT * FROM products WHERE category = 'Hot Deals'");
                while ($deal = $hot_deals->fetch_assoc()): 
            ?>
                <div class="deal">
                    <div class="deal-image">
                        <img src="images/<?= htmlspecialchars($deal["image_url"]) ?>" alt="<?= htmlspecialchars($deal["name"]) ?>">
                    </div>
                    <div class="deal-info">
                        <h3><?= htmlspecialchars($deal["description"]) ?></h3>
                        <p><strong><?= htmlspecialchars($deal["quantity"]) ?></strong> - <?= number_format($deal["price"], 2) ?> PHP</p>
                    </div>
                </div>
            <?php endwhile; ?>
        </section>

        <section class="menu">
            <h2>Our Menu</h2>
            <div class="menu-items">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="menu-item">
                        <div class="menu-box">
                            <div class="menu-image">
                                <img src="images/<?= htmlspecialchars($row["image_url"]) ?>" alt="<?= htmlspecialchars($row["name"]) ?>">
                            </div>
                            <p>
                                <strong><?= htmlspecialchars($row["name"]) ?></strong><br>
                                <?= nl2br(htmlspecialchars($row["description"])) ?><br>
                                <?= htmlspecialchars(number_format($row["price"], 2)) ?> PHP
                            </p>
                            <form method="post">
                                <input type="hidden" name="product_id" value="<?= $row["id"] ?>">
                                <button type="submit" class="order-button">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <button class="order-button">Order Now!</button>
        </section>
    </div>
</body>
</html>

<?php $conn->close(); ?>
