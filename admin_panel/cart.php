<?php
include '../components/connect.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Redirect to login page if not logged in
    header('Location: ../admin_panel/login1.php');
    exit();
}

// Remove item from cart
if (isset($_POST['delete_item'])) {
    $cart_id = $_POST['cart_id'];
    $cart_id = filter_var($cart_id, FILTER_SANITIZE_STRING);

    $verify_delete = $conn->prepare("SELECT * FROM cart WHERE id = ?");
    $verify_delete->execute([$cart_id]);

    if ($verify_delete->rowCount() > 0) {
        $delete_cart_item = $conn->prepare("DELETE FROM cart WHERE id = ?");
        $delete_cart_item->execute([$cart_id]);
        $success_msg[] = 'Item removed from cart';
    } else {
        $warning_msg[] = 'Item already removed';
    }
}

// Update cart quantity
if (isset($_POST['update_qty'])) {
    $cart_id = $_POST['cart_id'];
    $cart_id = filter_var($cart_id, FILTER_SANITIZE_STRING);
    $qty = $_POST['qty'];
    $qty = filter_var($qty, FILTER_SANITIZE_NUMBER_INT);
    
    // Validate quantity
    if ($qty > 0 && $qty <= 99) {
        $update_qty = $conn->prepare("UPDATE cart SET qty = ? WHERE id = ?");
        $update_qty->execute([$qty, $cart_id]);
        $success_msg[] = 'Cart quantity updated';
    } else {
        $warning_msg[] = 'Invalid quantity. Please enter a number between 1 and 99.';
    }
}

// Handle AJAX quantity updates
if (isset($_POST['ajax_update_qty'])) {
    $cart_id = $_POST['cart_id'];
    $qty = (int)$_POST['qty'];
    
    if ($qty > 0 && $qty <= 99) {
        $update_qty = $conn->prepare("UPDATE cart SET qty = ? WHERE id = ?");
        $update_qty->execute([$qty, $cart_id]);
        
        // Get updated cart data
        $select_cart_item = $conn->prepare("SELECT * FROM cart WHERE id = ?");
        $select_cart_item->execute([$cart_id]);
        $cart_item = $select_cart_item->fetch(PDO::FETCH_ASSOC);
        
        $sub_total = $cart_item['qty'] * $cart_item['price'];
        
        // Calculate grand total
        $grand_total = 0;
        $select_all_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
        $select_all_cart->execute([$user_id]);
        while($item = $select_all_cart->fetch(PDO::FETCH_ASSOC)) {
            $grand_total += $item['qty'] * $item['price'];
        }
        
        echo json_encode([
            'success' => true,
            'sub_total' => number_format($sub_total),
            'grand_total' => number_format($grand_total),
            'total_items' => $select_all_cart->rowCount()
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    }
    exit;
}

// Empty cart completely
if (isset($_POST['empty_cart'])) {
    $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $delete_cart->execute([$user_id]);
    $success_msg[] = 'Cart emptied successfully';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Gelato Ice</title>
    <!-- Load BoxIcons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --purple: #704264;
            --dark-purple: #603254;
            --light-purple: #B692C2;
            --box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f9f9f9;
        }

        .container {
            max-width: 1200px;
            margin: 80px auto 20px;
            padding: 20px;
        }

        header {
            background-color: var(--purple);
            color: white;
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: var(--box-shadow);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 10px;
            font-size: 1.8rem;
        }

        .navigation {
            display: flex;
            gap: 20px;
        }

        .navigation a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s ease;
        }

        .navigation a:hover {
            color: var(--light-purple);
        }

        .cart-box {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            padding: 20px;
        }

        h1 {
            text-align: center;
            padding: 20px 0;
            color: var(--purple);
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .cart-items {
            display: grid;
            gap: 20px;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto;
            align-items: center;
            gap: 20px;
            background-color: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .cart-item img {
            width: 100%;
            height: 80px;
            object-fit: contain;
            border-radius: 5px;
        }

        .item-details {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .item-name {
            font-weight: bold;
            font-size: 1.1rem;
            color: #333;
        }

        .item-price {
            color: var(--purple);
            font-weight: bold;
        }

        .item-subtotal {
            color: var(--purple);
            font-weight: 600;
        }

        .qty-control {
            display: flex;
            align-items: center;
            gap: 5px;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 5px;
        }

        .qty-btn {
            background-color: var(--purple);
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .qty-btn:hover {
            background-color: var(--dark-purple);
            transform: scale(1.1);
        }

        .qty-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .qty-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            background-color: white;
        }

        .qty-input:focus {
            outline: none;
            border-color: var(--purple);
        }

        .update-btn {
            background-color: var(--purple);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 12px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-left: 10px;
        }

        .update-btn:hover {
            background-color: var(--dark-purple);
        }

        .delete-form button {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s;
        }

        .delete-form button:hover {
            background-color: #c82333;
        }

        .cart-summary {
            margin-top: 30px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .summary-row.total {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--purple);
            border-bottom: none;
            margin-top: 15px;
        }

        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 20px;
        }

        .back-btn, .checkout-btn, .empty-cart-btn {
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: transform 0.2s, background-color 0.3s;
        }

        .back-btn {
            background-color: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
            flex: 1;
        }

        .checkout-btn {
            background-color: var(--purple);
            color: white;
            border: none;
            flex: 2;
        }

        .empty-cart-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            flex: 1;
        }

        .back-btn:hover, .checkout-btn:hover, .empty-cart-btn:hover {
            transform: translateY(-3px);
        }

        .back-btn:hover {
            background-color: #e5e5e5;
        }

        .checkout-btn:hover {
            background-color: var(--dark-purple);
        }

        .empty-cart-btn:hover {
            background-color: #c82333;
        }

        .empty-cart {
            text-align: center;
            padding: 50px 20px;
            font-size: 1.2rem;
            color: #777;
        }

        .empty-cart i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
            display: block;
        }

        .message-container {
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            width: 80%;
            max-width: 400px;
        }

        .message {
            padding: 10px 20px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
            animation: fadeOut 5s forwards;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .loading {
            opacity: 0.5;
            pointer-events: none;
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .cart-item {
                grid-template-columns: 80px 1fr;
                grid-template-areas:
                    "image details"
                    "image quantity"
                    "delete delete";
                padding: 15px 10px;
            }
            
            .cart-item img {
                grid-area: image;
            }
            
            .item-details {
                grid-area: details;
            }
            
            .qty-control {
                grid-area: quantity;
                margin-top: 10px;
            }
            
            .delete-form {
                grid-area: delete;
                margin-top: 15px;
                justify-self: end;
            }
            
            .cart-actions {
                flex-direction: column;
            }
            
            .back-btn, .checkout-btn, .empty-cart-btn {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 10px;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .logo {
                font-size: 1.2rem;
            }
            
            .navigation a {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header with navigation -->
    <header>
        <div class="header-container">
            <div class="logo">
                <i class='bx bx-ice-cream'></i>
                Gelato Ice
            </div>
            <div class="navigation">
                <a href="../admin_panel/view1_product.php">
                    <i class='bx bx-store'></i>
                    Catalog
                </a>
                <a href="../admin_panel/home.php" onclick="return confirm('Logout from this website?');">
                    <i class='bx bx-log-out'></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Messages display area -->
        <div class="message-container">
            <?php
            if(isset($success_msg)){
                foreach($success_msg as $success_msg){
                    echo '<div class="message success"><span>'.$success_msg.'</span></div>';
                }
            }
            if(isset($warning_msg)){
                foreach($warning_msg as $warning_msg){
                    echo '<div class="message warning"><span>'.$warning_msg.'</span></div>';
                }
            }
            ?>
        </div>

        <div class="cart-box">
            <h1>Shopping Cart</h1>
            
            <?php
            // Get cart items
            $grand_total = 0;
            $select_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            
            if($select_cart->rowCount() > 0){
            ?>
            
            <div class="cart-items">
                <?php
                while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                    // Get product details
                    $select_products = $conn->prepare("SELECT * FROM products WHERE id = ?");
                    $select_products->execute([$fetch_cart['product_id']]);
                    
                    if($select_products->rowCount() > 0){
                        $fetch_product = $select_products->fetch(PDO::FETCH_ASSOC);
                        $sub_total = $fetch_cart['qty'] * $fetch_cart['price'];
                        $grand_total += $sub_total;
                ?>
                
                <div class="cart-item" data-cart-id="<?= $fetch_cart['id']; ?>" data-price="<?= $fetch_cart['price']; ?>">
                    <img src="../image/<?= $fetch_product['image']; ?>" alt="<?= $fetch_product['name']; ?>">
                    
                    <div class="item-details">
                        <h3 class="item-name"><?= $fetch_product['name']; ?></h3>
                        <p class="item-price">Rp <?= number_format($fetch_cart['price']); ?>/-</p>
                        <p class="item-subtotal">Subtotal: Rp <span class="subtotal-amount"><?= number_format($sub_total); ?></span>/-</p>
                    </div>
                    
                    <div class="qty-control">
                        <button type="button" class="qty-btn decrease-qty" data-cart-id="<?= $fetch_cart['id']; ?>" <?= $fetch_cart['qty'] <= 1 ? 'disabled' : ''; ?>>
                            <i class='bx bx-minus'></i>
                        </button>
                        <input type="number" class="qty-input" min="1" max="99" value="<?= $fetch_cart['qty']; ?>" data-cart-id="<?= $fetch_cart['id']; ?>">
                        <button type="button" class="qty-btn increase-qty" data-cart-id="<?= $fetch_cart['id']; ?>" <?= $fetch_cart['qty'] >= 99 ? 'disabled' : ''; ?>>
                            <i class='bx bx-plus'></i>
                        </button>
                        <form action="" method="post" style="display: inline;">
                            <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
                            <input type="hidden" name="qty" class="manual-qty" value="<?= $fetch_cart['qty']; ?>">
                            <button type="submit" name="update_qty" class="update-btn" title="Update manually">
                                <i class='bx bx-refresh'></i>
                            </button>
                        </form>
                    </div>
                    
                    <form action="" method="post" class="delete-form">
                        <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
                        <button type="submit" name="delete_item" onclick="return confirm('Remove this item from cart?');">
                            <i class='bx bx-trash'></i>
                        </button>
                    </form>
                </div>
                
                <?php
                    }
                }
                ?>
            </div>
            
            <div class="cart-summary">
                <div class="summary-row">
                    <span>Items in cart:</span>
                    <span id="total-items"><?= $select_cart->rowCount(); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Grand Total:</span>
                    <span>Rp <span id="grand-total"><?= number_format($grand_total); ?></span>/-</span>
                </div>
            </div>
            
            <div class="cart-actions">
                <a href="../admin_panel/view1_product.php" class="back-btn">
                    <i class='bx bx-arrow-back'></i> Continue Shopping
                </a>
                
                <form action="" method="post" style="flex: 1;">
                    <button type="submit" name="empty_cart" class="empty-cart-btn" onclick="return confirm('Are you sure you want to empty your cart?');">
                        <i class='bx bx-trash-alt'></i> Empty Cart
                    </button>
                </form>
                
                <a href="../components/checkout.php" class="checkout-btn">
                    <i class='bx bx-credit-card'></i> Proceed to Checkout
                </a>
            </div>
            
            <?php
            }else{
            ?>
            
            <div class="empty-cart">
                <i class='bx bx-cart'></i>
                <p>Your cart is empty</p>
                <a href="../admin_panel/view1_product.php" class="back-btn" style="margin-top: 20px; display: inline-block;">
                    <i class='bx bx-arrow-back'></i> Continue Shopping
                </a>
            </div>
            
            <?php
            }
            ?>
        </div>
    </div>

    <script>
        // Auto remove messages after 5 seconds
        setTimeout(function(){
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                message.style.display = 'none';
            });
        }, 5000);

        // Function to update quantity via AJAX
        function updateQuantity(cartId, newQty) {
            const cartItem = document.querySelector(`[data-cart-id="${cartId}"]`);
            const subtotalElement = cartItem.querySelector('.subtotal-amount');
            const price = parseFloat(cartItem.dataset.price);
            
            // Add loading state
            cartItem.classList.add('loading');
            
            // Create FormData
            const formData = new FormData();
            formData.append('ajax_update_qty', '1');
            formData.append('cart_id', cartId);
            formData.append('qty', newQty);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update subtotal
                    subtotalElement.textContent = data.sub_total;
                    
                    // Update grand total
                    document.getElementById('grand-total').textContent = data.grand_total;
                    
                    // Update total items count
                    document.getElementById('total-items').textContent = data.total_items;
                    
                    // Update button states
                    const decreaseBtn = cartItem.querySelector('.decrease-qty');
                    const increaseBtn = cartItem.querySelector('.increase-qty');
                    const qtyInput = cartItem.querySelector('.qty-input');
                    const manualQtyInput = cartItem.querySelector('.manual-qty');
                    
                    decreaseBtn.disabled = newQty <= 1;
                    increaseBtn.disabled = newQty >= 99;
                    qtyInput.value = newQty;
                    manualQtyInput.value = newQty;
                } else {
                    alert(data.message || 'Error updating quantity');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating quantity');
            })
            .finally(() => {
                // Remove loading state
                cartItem.classList.remove('loading');
            });
        }

        // Handle increase quantity
        document.addEventListener('click', function(e) {
            if (e.target.closest('.increase-qty')) {
                const button = e.target.closest('.increase-qty');
                const cartId = button.dataset.cartId;
                const qtyInput = document.querySelector(`[data-cart-id="${cartId}"] .qty-input`);
                const currentQty = parseInt(qtyInput.value);
                
                if (currentQty < 99) {
                    updateQuantity(cartId, currentQty + 1);
                }
            }
        });

        // Handle decrease quantity
        document.addEventListener('click', function(e) {
            if (e.target.closest('.decrease-qty')) {
                const button = e.target.closest('.decrease-qty');
                const cartId = button.dataset.cartId;
                const qtyInput = document.querySelector(`[data-cart-id="${cartId}"] .qty-input`);
                const currentQty = parseInt(qtyInput.value);
                
                if (currentQty > 1) {
                    updateQuantity(cartId, currentQty - 1);
                }
            }
        });

        // Handle direct input change
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('qty-input')) {
                const cartId = e.target.dataset.cartId;
                let newQty = parseInt(e.target.value);
                
                // Validate quantity
                if (isNaN(newQty) || newQty < 1) {
                    newQty = 1;
                } else if (newQty > 99) {
                    newQty = 99;
                }
                
                updateQuantity(cartId, newQty);
            }
        });

        // Prevent invalid input
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('qty-input')) {
                let value = e.target.value;
                if (value.length > 2) {
                    e.target.value = value.slice(0, 2);
                }
            }
        });

        // Handle keypress for quantity input
        document.addEventListener('keypress', function(e) {
            if (e.target.classList.contains('qty-input')) {
                if (e.target.value.length >= 2) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>