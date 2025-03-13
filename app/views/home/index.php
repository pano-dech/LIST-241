<div class="container">
    <section class="welcome-section">
        <h2>Welcome to Tummy Pillow Bakery</h2>
        <p class="lead">Where there's comfort in every bite!</p>
        
        <div class="cta-buttons">
            <a href="/menu" class="btn btn-primary">Browse Our Menu</a>
            <?php if (!$is_logged_in): ?>
                <a href="/register" class="btn btn-secondary">Register Now</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="hot-deals">
        <h2>Hot Deals</h2>
        <p>Check out our special offers this week!</p>
        
        <div class="product-grid">
            <?php foreach ($hot_deals as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="/images/products/<?= $this->e($product['image_url']) ?>" alt="<?= $this->e($product['name']) ?>">
                    </div>
                    <div class="product-info">
                        <h3><?= $this->e($product['name']) ?></h3>
                        <p class="product-quantity"><?= $this->e($product['quantity_desc']) ?></p>
                        <p class="product-price"><?= $this->formatPrice($product['price']) ?></p>
                        <div class="product-actions">
                            <a href="/menu/product/<?= $product['id'] ?>" class="btn btn-info">View Details</a>
                            
                            <?php if ($is_logged_in): ?>
                                <form action="/cart/add" method="post">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="return_url" value="/">
                                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <a href="/login" class="btn btn-primary">Login to Order</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="center-button">
            <a href="/menu" class="btn btn-primary">View All Products</a>
        </div>
    </section>

    <section class="features">
        <div class="feature">
            <div class="feature-icon">
                <i class="fas fa-bread-slice"></i>
            </div>
            <h3>Fresh Ingredients</h3>
            <p>We use only the freshest, highest quality ingredients in all our baked goods.</p>
        </div>
        
        <div class="feature">
            <div class="feature-icon">
                <i class="fas fa-truck"></i>
            </div>
            <h3>Fast Delivery</h3>
            <p>Get your freshly baked treats delivered directly to your doorstep.</p>
        </div>
        
        <div class="feature">
            <div class="feature-icon">
                <i class="fas fa-heart"></i>
            </div>
            <h3>Made with Love</h3>
            <p>Every product is crafted with passion and care for your satisfaction.</p>
        </div>
    </section>

    <section class="testimonials">
        <h2>What Our Customers Say</h2>
        
        <div class="testimonial-slider">
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"The best bread I've ever tasted! Their Garlic Cream Cheese Buns are absolutely divine."</p>
                </div>
                <div class="testimonial-author">
                    <span>Sarah T.</span>
                </div>
            </div>
            
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"Quick delivery and the pastries were still warm when they arrived. Amazing service!"</p>
                </div>
                <div class="testimonial-author">
                    <span>Mark R.</span>
                </div>
            </div>
            
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"Tummy Pillow has been our go-to bakery for family events. Everyone loves their products!"</p>
                </div>
                <div class="testimonial-author">
                    <span>Anna M.</span>
                </div>
            </div>
        </div>
    </section>
</div>