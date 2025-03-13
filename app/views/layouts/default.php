<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Tummy Pillow Bakery' ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <!-- Add a modern font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="/">
                    <img src="/images/logo.png" alt="Tummy Pillow Bakery">
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="/about">Who We Are</a></li>
                    <li><a href="/menu">Menu</a></li>
                    <?php if ($is_logged_in): ?>
                        <li><a href="/profile">Profile</a></li>
                        <?php if ($is_admin): ?>
                            <li><a href="/admin/orders">Admin</a></li>
                        <?php endif; ?>
                        <li><a href="/logout">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/login">Login</a></li>
                        <li><a href="/register">Register</a></li>
                    <?php endif; ?>
                    <li>
                        <a href="/cart" class="cart-button">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count" id="cart-count"><?= $cart_count ?></span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <!-- Hero Image on Home Page Only -->
    <?php if (isset($show_hero) && $show_hero): ?>
    <div class="hero">
        <img src="/images/hero.jpg" alt="Delicious baked goods">
        <div class="hero-content">
            <h1>Welcome to Tummy Pillow</h1>
            <p>Where there's comfort in every bite!</p>
            <a href="/menu" class="btn btn-primary">View Our Menu</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Flash Messages -->
    <?php if ($this->session->hasFlash('success')): ?>
        <div class="flash-message success">
            <div class="container">
                <i class="fas fa-check-circle"></i> <?= $this->e($this->session->getFlash('success')) ?>
                <button class="close-flash"><i class="fas fa-times"></i></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($this->session->hasFlash('error')): ?>
        <div class="flash-message error">
            <div class="container">
                <i class="fas fa-exclamation-circle"></i> <?= $this->e($this->session->getFlash('error')) ?>
                <button class="close-flash"><i class="fas fa-times"></i></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($this->session->hasFlash('warning')): ?>
        <div class="flash-message warning">
            <div class="container">
                <i class="fas fa-exclamation-triangle"></i> <?= $this->e($this->session->getFlash('warning')) ?>
                <button class="close-flash"><i class="fas fa-times"></i></button>
            </div>
        </div>
    <?php endif; ?>

    <main>
        <?= $content ?>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <h3>Tummy Pillow</h3>
                    <p>Where there's comfort in every bite!</p>
                    <p class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </p>
                </div>
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/about">Who We Are</a></li>
                        <li><a href="/menu">Menu</a></li>
                        <li><a href="/cart">Cart</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Bakery Street, Manila</p>
                    <p><i class="fas fa-phone"></i> (02) 8123-4567</p>
                    <p><i class="fas fa-envelope"></i> info@tummypillow.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Tummy Pillow Bakery. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="/js/main.js"></script>
    <?php if (isset($cart_page) && $cart_page): ?>
        <script src="/js/cart.js"></script>
    <?php endif; ?>
</body>
</html>