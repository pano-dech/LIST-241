/**
 * Cart functionality
 */
document.addEventListener('DOMContentLoaded', () => {
    // Update quantity buttons
    const quantityControls = document.querySelectorAll('.quantity-controls');
    
    quantityControls.forEach(control => {
        const itemId = control.dataset.itemId;
        const decreaseBtn = control.querySelector('.decrease');
        const increaseBtn = control.querySelector('.increase');
        const quantityElement = control.querySelector('.quantity');
        
        // Decrease quantity
        decreaseBtn.addEventListener('click', () => {
            const currentQuantity = parseInt(quantityElement.textContent);
            
            if (currentQuantity > 1) {
                updateCartItem(itemId, currentQuantity - 1);
            } else {
                // Confirm removal
                if (confirm('Are you sure you want to remove this item from your cart?')) {
                    updateCartItem(itemId, 0);
                }
            }
        });
        
        // Increase quantity
        increaseBtn.addEventListener('click', () => {
            const currentQuantity = parseInt(quantityElement.textContent);
            updateCartItem(itemId, currentQuantity + 1);
        });
    });
    
    // Function to update cart item quantity
    function updateCartItem(itemId, quantity) {
        // Show loading state
        document.querySelector(`#quantity-${itemId}`).classList.add('loading');
        
        // Send AJAX request
        fetch('/cart/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `item_id=${itemId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            // Handle response
            if (data.success) {
                // Update quantity and subtotal
                if (data.new_quantity > 0) {
                    document.querySelector(`#quantity-${itemId}`).textContent = data.new_quantity;
                    document.querySelector(`#subtotal-${itemId}`).textContent = formatPrice(data.subtotal);
                } else {
                    // Remove row if quantity is 0
                    const row = document.querySelector(`#cart-row-${itemId}`);
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        
                        // Show empty cart message if no items left
                        if (document.querySelectorAll('.cart-table tbody tr').length === 0) {
                            document.querySelector('.cart-content').innerHTML = `
                                <div class="empty-cart">
                                    <div class="empty-cart-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <h2>Your cart is empty</h2>
                                    <p>Looks like you haven't added any items to your cart yet.</p>
                                    <a href="/menu" class="btn btn-primary">Browse Our Menu</a>
                                </div>
                            `;
                        }
                    }, 300);
                }
                
                // Update cart total
                document.querySelector('#cart-subtotal').textContent = formatPrice(data.cart_total);
                document.querySelector('#cart-total').textContent = formatPrice(data.cart_total);
                
                // Update cart count in header
                document.querySelector('#cart-count').textContent = data.cart_count;
            } else {
                // Show error
                alert(data.message || 'Failed to update cart. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error updating cart:', error);
            alert('An error occurred while updating the cart. Please try again.');
        })
        .finally(() => {
            // Remove loading state
            document.querySelector(`#quantity-${itemId}`).classList.remove('loading');
        });
    }
    
    // Format price
    function formatPrice(price) {
        return parseFloat(price).toFixed(2) + ' PHP';
    }
    
    // Confirm cart clear
    const clearForm = document.querySelector('.clear-form');
    
    if (clearForm) {
        clearForm.addEventListener('submit', (e) => {
            if (!confirm('Are you sure you want to clear your cart? This will remove all items.')) {
                e.preventDefault();
            }
        });
    }
});