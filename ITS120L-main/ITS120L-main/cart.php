


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Tummy Pillow</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function updateCart(action, id) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=${action}&id=${encodeURIComponent(id)}`
            })
            .then(response => response.text())
            .then(() => location.reload());
        }
    </script>
</head>
<body>
    <div class="container">
	<header>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';
include 'header.php';

// Fetch cart items from the database (items with count > 0)
$query = $conn->query("SELECT * FROM products WHERE count > 0");

$cart_items = [];
while ($row = $query->fetch_assoc()) {
    $cart_items[] = $row;
}
?>
</header>
        <section class="cart-section">
            <h2>Your Cart</h2>
            
            <?php if (count($cart_items) > 0): ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;

                        foreach ($cart_items as $item):
                            $subtotal = $item["price"] * $item["count"];
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item["name"]) ?></td>
                            <td><?= number_format($item["price"], 2) ?> PHP</td>
                            <td>
                                <button class="qty-btn" onclick="updateCart('decrease', <?= $item['id'] ?>)">−</button>
                                <?= (int) $item["count"] ?>  
                                <button class="qty-btn" onclick="updateCart('increase', <?= $item['id'] ?>)">+</button>
                            </td>
                            <td><?= number_format($subtotal, 2) ?> PHP</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="cart-total-label"><strong>Grand Total:</strong></td>
                            <td class="cart-total-value"><strong><?= number_format($total, 2) ?> PHP</strong></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="cart-actions">
                    <form method="post" action="clear_cart.php">
                        <button type="submit" class="clear-cart">Clear Cart</button>
                    </form>
                    <a href="checkout.php" class="checkout-button">Proceed to Checkout</a>
                </div>
            <?php else: ?>
                <p class="empty-cart">Your cart is empty.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
