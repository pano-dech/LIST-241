<div class="container">
    <section class="menu-header">
        <h1>Our Menu</h1>
        <p>Discover our delicious selection of freshly baked goods</p>
        
        <div class="search-bar">
            <form action="/menu/search" method="get">
                <input type="text" name="keyword" placeholder="Search our menu..." required>
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        
        <div class="category-navigation">
            <ul class="category-tabs">
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a href="#category-<?= $category['id'] ?>" class="category-tab">
                            <?= $this->e($category['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <?php foreach ($productsByCategory as $categoryId => $categoryData): ?>
        <section id="category-<?= $categoryId ?>" class="menu-category">
            <h2><?= $this->e($categoryData['category']['name']) ?></h2>
            <?php if (!empty($categoryData['category']['description'])): ?>
                <p class="category-description"><?= $this->e($categoryData['category']['description']) ?></p>
            <?php endif; ?>
            
            <div class="product-grid">
                <?php foreach ($categoryData['products'] as $product): ?>
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
                                        <input type="hidden" name="return_url" value="/menu">
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
        </section>
    <?php endforeach; ?>

    <section class="custom-orders">
        <div class="custom-order-card">
            <div class="custom-order-icon">
                <i class="fas fa-birthday-cake"></i>
            </div>
            <div class="custom-order-content">
                <h3>Special Orders</h3>
                <p>Need a custom cake or large order for an event? We can help!</p>
                <p>Contact us at <strong>info@tummypillow.com</strong> or call <strong>(02) 8123-4567</strong></p>
                <p><small>* Please allow at least 3-5 days advance notice for special orders</small></p>
            </div>
        </div>
    </section>
</div>