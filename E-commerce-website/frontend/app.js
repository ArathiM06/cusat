// app.js - script file for cart and api functions
const API_BASE_URL = 'http://127.0.0.1:8000/api';

// toast popup manager
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    
    // basic text alert headers instead of heavy svgs
    let alertLabel = '[ SUCCESS ] ';
    if (type != 'success') {
        alertLabel = '[ ERROR ] ';
    }

    toast.className = 'toast toast-' + type;
    toast.innerHTML = '<b>' + alertLabel + '</b> <span>' + message + '</span>';
    container.appendChild(toast);

    // make it show up
    setTimeout(function() {
        toast.classList.add('show');
    }, 10);

    // clear it out later
    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 3000);
}

// local storage read helper
function getCart() {
    try {
        let data = localStorage.getItem('cusat_cart');
        if (data == null) {
            return []; // returning empty array
        }
        return JSON.parse(data) || [];
    } catch(err) {
        return [];
    }
}

// local storage save helper
function saveCart(cart) {
    localStorage.setItem('cusat_cart', JSON.stringify(cart));
    updateCartBadge();
}

// add button click handler
function addToCart(productId, name, price, imageUrl) {
    let cart = getCart();
    let existingIndex = -1;
    
    // classic manual check for item match
    for (let i = 0; i < cart.length; i++) {
        if (cart[i].product_id == productId) {
            existingIndex = i;
            break;
        }
    }

    if (existingIndex > -1) {
        cart[existingIndex].quantity = cart[existingIndex].quantity + 1;
    } else {
        cart.push({
            product_id: productId,
            name: name,
            price: price,
            image_url: imageUrl,
            quantity: 1
        });
    }

    saveCart(cart);
    showToast('Added ' + name + ' to cart!', 'success');
}

// remove item completely
function removeFromCart(productId) {
    let cart = getCart();
    let tempCart = [];
    
    for (let i = 0; i < cart.length; i++) {
        if (cart[i].product_id != productId) {
            tempCart.push(cart[i]);
        }
    }
    cart = tempCart;
    saveCart(cart);
    
    if (window.location.pathname.indexOf('cart.php') != -1) {
        renderCartPage();
    }
    showToast("Item removed from cart.", "success");
}

// plus or minus items inside cart page
function updateQuantity(productId, delta) {
    let cart = getCart();
    let matchIndex = -1;

    for (let i = 0; i < cart.length; i++) {
        if (cart[i].product_id == productId) {
            matchIndex = i;
            break;
        }
    }

    if (matchIndex > -1) {
        cart[matchIndex].quantity = cart[matchIndex].quantity + delta;
        if (cart[matchIndex].quantity <= 0) {
            // drop it out if zero
            removeFromCart(productId);
            return;
        }
        saveCart(cart);
        if (window.location.pathname.indexOf('cart.php') != -1) {
            renderCartPage();
        }
    }
}

// clear function
function clearCart() {
    localStorage.removeItem('cusat_cart');
    updateCartBadge();
}

// updates the number on basket
function updateCartBadge() {
    const badge = document.getElementById('cart-badge');
    if (!badge) return;

    const cart = getCart();
    let totalCount = 0;
    
    for (let i = 0; i < cart.length; i++) {
        totalCount = totalCount + cart[i].quantity;
    }

    if (totalCount > 0) {
        badge.textContent = totalCount;
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}

// builds the cart page contents dynamically
function renderCartPage() {
    const cartItemsContainer = document.getElementById('cart-items-list');
    const orderItemsInput = document.getElementById('order-items-input');
    const summarySubtotal = document.getElementById('summary-subtotal');
    const summaryTotal = document.getElementById('summary-total');
    const checkoutBtn = document.getElementById('checkout-btn');
    const checkoutForm = document.getElementById('checkout-form');
    
    if (!cartItemsContainer) return;

    const cart = getCart();
    
    if (cart.length == 0) {
        cartItemsContainer.innerHTML = '<div style="text-align:center; padding:30px;">' +
            '<h3>Your Cart is Empty</h3>' +
            '<p>Add some custom CUSAT merchandise or textbook books to get started!</p>' +
            '<br><a href="index.php" style="color:blue; font-weight:bold;">Go to Catalog</a>' +
            '</div>';
            
        if (checkoutBtn) checkoutBtn.disabled = true;
        if (summarySubtotal) summarySubtotal.textContent = '₹0.00';
        if (summaryTotal) summaryTotal.textContent = '₹0.00';
        if (checkoutForm) checkoutForm.style.opacity = '0.4';
        return;
    }

    if (checkoutBtn) checkoutBtn.disabled = false;
    if (checkoutForm) checkoutForm.style.opacity = '1';

    let outHtml = '';
    let grandTotal = 0;

    for (let j = 0; j < cart.length; j++) {
        let currentItem = cart[j];
        let rowTotal = currentItem.price * currentItem.quantity;
        grandTotal = grandTotal + rowTotal;

        outHtml += '<div class="cart-item" style="border-bottom:1px solid #ccc; padding:10px 0px;">' +
            '<table width="100%">' +
            '<tr>' +
            '<td width="60px"><img src="' + currentItem.image_url + '" width="50px" height="50px" style="object-fit:cover;"></td>' +
            '<td>' +
            '<b>' + currentItem.name + '</b><br>' +
            '<span style="color:green;">₹' + currentItem.price + '</span>' +
            '</td>' +
            '<td align="right">' +
            '<button class="qty-btn" onclick="updateQuantity(' + currentItem.product_id + ', -1)">-</button> ' +
            '<span class="qty-val"><b>' + currentItem.quantity + '</b></span> ' +
            '<button class="qty-btn" onclick="updateQuantity(' + currentItem.product_id + ', 1)">+</button>' +
            '&nbsp;&nbsp;&nbsp;&nbsp;' +
            '<button class="remove-btn" onclick="removeFromCart(' + currentItem.product_id + ')" style="color:red; background:none; border:none; cursor:pointer;">[ Remove ]</button>' +
            '</td>' +
            '</tr>' +
            '</table>' +
            '</div>';
    }

    cartItemsContainer.innerHTML = outHtml;
    if (summarySubtotal) summarySubtotal.textContent = '₹' + grandTotal.toFixed(2);
    if (summaryTotal) summaryTotal.textContent = '₹' + grandTotal.toFixed(2);

    // insert values inside stringified input block
    if (orderItemsInput) {
        let simpleList = [];
        for (let k = 0; k < cart.length; k++) {
            simpleList.push({
                product_id: cart[k].product_id,
                quantity: cart[k].quantity
            });
        }
        orderItemsInput.value = JSON.stringify(simpleList);
    }
}

// submit checkout parameters to api server
async function handleCheckout(event) {
    event.preventDefault();

    const cart = getCart();
    if (cart.length == 0) {
        showToast("Your cart is empty!", "error");
        return;
    }

    const form = event.target;
    const formData = new FormData(form);

    let itemsPayload = [];
    for (let x = 0; x < cart.length; x++) {
        itemsPayload.push({
            product_id: parseInt(cart[x].product_id),
            quantity: parseInt(cart[x].quantity)
        });
    }

    let userIdVal = null;
    if (formData.get('user_id')) {
        userIdVal = parseInt(formData.get('user_id'));
    }

    const orderData = {
        user_id: userIdVal,
        customer_name: formData.get('customer_name'),
        customer_email: formData.get('customer_email'),
        customer_phone: formData.get('customer_phone'),
        department: formData.get('department'),
        roll_number: formData.get('roll_number'),
        delivery_address: formData.get('delivery_address'),
        items: itemsPayload
    };

    const submitBtn = document.getElementById('checkout-btn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.value = 'Processing Order...';
    }

    try {
        const response = await fetch(API_BASE_URL + '/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        });

        if (response.ok == false) {
            const errData = await response.json();
            throw new Error(errData.detail || 'Failed to place order');
        }

        const orderResult = await response.json();

        // simple link redirect mapping
        const redirectUrl = 'cart.php?success=1' +
            '&order_id=' + orderResult.id +
            '&name=' + encodeURIComponent(orderResult.customer_name) +
            '&total=' + orderResult.total_amount +
            '&date=' + encodeURIComponent(orderResult.created_at);

        clearCart();
        window.location.href = redirectUrl;

    } catch (error) {
        showToast(error.message, 'error');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.value = 'Place Order (Cash on Delivery)';
        }
    }
}

// delete item execution block for merchant control board
async function deleteProduct(productId, adminToken) {
    if (confirm("Are you sure you want to delete this product from the catalog?") == false) {
        return;
    }

    try {
        const response = await fetch(API_BASE_URL + '/products/' + productId, {
            method: 'DELETE',
            headers: {
                'x-admin-token': adminToken
            }
        });

        if (response.ok == false) {
            const errData = await response.json();
            throw new Error(errData.detail || 'Failed to delete product');
        }

        showToast("Product deleted successfully", "success");
        setTimeout(function() {
            window.location.reload();
        }, 1000);

    } catch (error) {
        showToast(error.message, 'error');
    }
}

// add entry item into catalog database logic
async function handleAddProduct(event, adminToken) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    const productData = {
        name: formData.get('name'),
        price: parseFloat(formData.get('price')),
        category: formData.get('category'),
        description: formData.get('description'),
        image_url: formData.get('image_url') || null
    };

    const submitBtn = form.querySelector('input[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    try {
        const response = await fetch(API_BASE_URL + '/products', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'x-admin-token': adminToken
            },
            body: JSON.stringify(productData)
        });

        if (response.ok == false) {
            const errData = await response.json();
            throw new Error(errData.detail || 'Failed to add product');
        }

        showToast("Product added successfully!", "success");
        form.reset();
        
        setTimeout(function() {
            window.location.reload();
        }, 1000);

    } catch (error) {
        showToast(error.message, 'error');
        if (submitBtn) submitBtn.disabled = false;
    }
}

// system start loop trigger
document.addEventListener('DOMContentLoaded', function() {
    updateCartBadge();
    
    if (window.location.pathname.indexOf('cart.php') != -1) {
        renderCartPage();
        
        const checkoutForm = document.getElementById('checkout-form');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', handleCheckout);
        }
    }
});
