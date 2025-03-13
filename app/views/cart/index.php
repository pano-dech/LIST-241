<div class="container">
    <section class="cart-section">
        <h1>Your Shopping Cart</h1>
        
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="/menu" class="btn btn-primary">Browse Our Menu</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr id="cart-row-<?= $item['id'] ?>">
                                    <td class="product-cell">
                                        <div class="cart-product">
                                            <img src="/images/products/<?= $this->e($item['image_url']) ?>" alt="<?= $this->e($item['name']) ?>">
                                            <div class="cart-product-info">
                                                <h3><?= $this->e($item['name']) ?></h3>
                                                <p><?= $this->e($item['quantity_desc']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price-cell">
                                        <?= $this->formatPrice($item['price']) ?>
                                    </td>
                                    <td class="quantity-cell">
                                        <div class="quantity-controls" data-item-id="<?= $item['id'] ?>">
                                            <button type="button" class="quantity-btn decrease">-</button>
                                            <span class="quantity" id="quantity-<?= $item['id'] ?>"><?= $item['quantity'] ?></span>
                                            <button type="button" class="quantity-btn increase">+</button>
                                        </div>
                                    </td>
                                    <td class="subtotal-cell" id="subtotal-<?= $item['id'] ?>">
                                        <?= $this->formatPrice($item['subtotal']) ?>
                                    </td>
                                    <td class="action-cell">
                                        <form action="/cart/remove" method="post">
                                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="cart-subtotal"><?= $this->formatPrice($cartTotal) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee:</span>
                        <span>To be calculated</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span id="cart-total"><?= $this->formatPrice($cartTotal) ?></span>
                    </div>
                    <div class="cart-actions">
                        <form action="/cart/clear" method="post" class="clear-form">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <button type="submit" class="btn btn-outline">Clear Cart</button>
                        </form>
                        <a href="/cart/checkout" class="btn btn-primary">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
    // Mark this page as a cart page to load the cart.js script
    window.isCartPage = true;
</script>