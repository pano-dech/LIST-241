<?php    
session_start();
$conn = new mysqli("localhost", "root", "", "tummy_pillow_db");

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert predefined products if they do not exist
$products = [
    [1, 'Hot Deals', 'Garlic Cream Cheese Buns - Box of 6 Regular Size Buns', 'gccb1.jpg', 420.00, 'box of 6'],
    [2, 'Hot Deals', 'Garlic Cream Cheese Buns - Box of 4 Big Size Buns', 'gccb1.jpg', 420.00, 'box of 4'],
    [3, 'Hot Deals', 'Chocolate Revel Bars - Box of 16 Bars', 'crb2.jpg', 440.00, 'box of 16'],
    [4, 'Menu', 'Cinnamon Rolls - Box of 4', 'cr3.jpg', 420.00, 'box of 4'],
    [5, 'Menu', 'Empanadas - Box of 4', 'empa1.jpg', 260.00, 'box of 4'],
    [6, 'Menu', 'Empanadas - Box of 12', 'empa1.jpg', 780.00, 'box of 12']
];

$stmt = $conn->prepare("INSERT INTO menu_products (product_id, category, name, image_url, price, quantity, status, stock) 
    SELECT ?, ?, ?, ?, ?, ?, 'Available', 10 
    FROM DUAL WHERE NOT EXISTS (
        SELECT 1 FROM menu_products WHERE product_id = ?
    )");
    
foreach ($products as $product) {
    list($product_id, $category, $name, $image_url, $price, $quantity) = $product;
    $stmt->bind_param("isssdis", $product_id, $category, $name, $image_url, $price, $quantity, $product_id);
    $stmt->execute();
}
$stmt->close();

// Handle Add to Cart request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["product_id"])) {
    $product_id = intval($_POST["product_id"]);
    $user_id = $_SESSION['user_id'] ?? null;

    if ($user_id) {
        $stmt = $conn->prepare("SELECT quantity FROM cart WHERE product_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE product_id = ? AND user_id = ?");
        } else {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        }
        
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "<script>alert('Please log in to add items to your cart.');</script>";
    }
}

// Count total cart items
$user_id = intval($_SESSION['user_id'] ?? 0);
$countQuery = $conn->query("SELECT SUM(quantity) AS cart_count FROM cart WHERE user_id = $user_id");
$countRow = $countQuery->fetch_assoc();
$count = $countRow["cart_count"] ?? 0;

// Fetch products from database
$menuResult = $conn->query("SELECT * FROM menu_products WHERE category = 'Menu' AND status = 'Available'");
$hotDealsResult = $conn->query("SELECT * FROM menu_products WHERE category = 'Hot Deals' AND status = 'Available'");
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
                <a href="profile.php">Profile</a>
                <a href="cart.php">
                    <button class="cart-button">Cart (<span id="cart-count"><?= $count ?></span>)</button>
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
            <?php while ($deal = $hotDealsResult->fetch_assoc()): ?>
                <div class="deal">
                    <div class="deal-image">
                        <img src="images/<?= htmlspecialchars($deal["image_url"]) ?>" alt="<?= htmlspecialchars($deal["name"]) ?>">
                    </div>
                    <div class="deal-info">
                        <h3><?= htmlspecialchars($deal["name"]) ?></h3>
                        <p><strong><?= htmlspecialchars($deal["quantity"]) ?></strong> - <?= number_format($deal["price"], 2) ?> PHP</p>
                    </div>
                </div>
            <?php endwhile; ?>
        </section>

        <section class="menu">
            <h2>Our Menu</h2>
            <div class="menu-items">
                <?php while ($row = $menuResult->fetch_assoc()): ?>
                    <div class="menu-item">
                        <div class="menu-box">
                            <div class="menu-image">
                                <img src="images/<?= htmlspecialchars($row["image_url"]) ?>" alt="<?= htmlspecialchars($row["name"]) ?>">
                            </div>
                            <p>
                                <strong><?= htmlspecialchars($row["name"]) ?></strong><br>
                                <?= number_format($row["price"], 2) ?> PHP
                            </p>
                            <form method="post">
                                <input type="hidden" name="product_id" value="<?= $row["product_id"] ?>">
                                <button type="submit" class="order-button">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </div>
</body>
</html>

<?php $conn->close(); ?>
