function getCart() {
    return JSON.parse(localStorage.getItem('cart') || '[]');
}

function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function addToCart(item) {
    const cart = getCart();
    const existing = cart.find(c => c.menu_item_id === item.id);
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({ menu_item_id: item.id, name: item.name, price: parseFloat(item.price), quantity: 1 });
    }
    saveCart(cart);
}

function removeFromCart(menuItemId) {
    saveCart(getCart().filter(c => c.menu_item_id !== menuItemId));
}

function updateCartQuantity(menuItemId, delta) {
    const cart = getCart();
    const item = cart.find(c => c.menu_item_id === menuItemId);
    if (!item) return;
    item.quantity += delta;
    if (item.quantity <= 0) {
        saveCart(cart.filter(c => c.menu_item_id !== menuItemId));
    } else {
        saveCart(cart);
    }
}

function clearCart() {
    localStorage.removeItem('cart');
}

function cartItemCount() {
    return getCart().reduce((sum, c) => sum + c.quantity, 0);
}

function updateCartBadge() {
    const badge = document.getElementById('cart-badge');
    if (!badge) return;
    const count = cartItemCount();
    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
}
