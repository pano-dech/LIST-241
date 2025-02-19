<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cart_count = isset($_SESSION["cart"]) ? array_sum(array_column($_SESSION["cart"], "quantity")) : 0;
?>

<div class="container">
    <header>
        <div class="logo-container">
            <a href="index.php">
                <img src="images/logo.png" alt="Tummy Pillow Logo">
            </a>
        </div>
        <nav>
            <a href="who-we-are.php">Who We Are</a>
            <a href="menu.php">Menu</a>
            <a href="profile.html">Profile</a>
            <a href="cart.php">
                <button class="cart-button">Cart (<?= $cart_count ?>)</button>
            </a>
        </nav>
    </header>
    <section class="hero">
        <img src="images/gccb2.jpg" alt="Bread and Flowers">
    </section>
</div>
