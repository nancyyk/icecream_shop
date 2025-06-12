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
    header('Location: ../components/login.php');
    exit();
}

// Initialize variables
$grand_total = 0;
$address_type = '';
$payment_method = '';

// Check if individual product is selected for direct checkout
if (isset($_GET['get_id'])) {
    $get_id = $_GET['get_id'];
    
    // Get product details
    $get_product = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
    $get_product->execute([$get_id]);
    
    if ($get_product->rowCount() > 0) {
        $fetch_product = $get_product->fetch(PDO::FETCH_ASSOC);
        $grand_total = $fetch_product['price'];
    } else {
        header('location:../admin_panel/view1_product.php');
        exit();
    }
} else {
    // Check cart for items
    $check_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
    $check_cart->execute([$user_id]);
    
    if ($check_cart->rowCount() <= 0) {
        header('location:../admin_panel/view1_product.php');
        exit();
    }
}

// Success and warning messages
$success_msg = [];
$warning_msg = [];

// Place order
if (isset($_POST['place_order'])) {
    // Validate and sanitize form data
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $address_type = filter_var($_POST['address_type'], FILTER_SANITIZE_STRING);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $address2 = isset($_POST['address2']) ? filter_var($_POST['address2'], FILTER_SANITIZE_STRING) : '';
    $payment_method = filter_var($_POST['payment_method'], FILTER_SANITIZE_STRING);
    
    // Gabungkan address dengan address_type
    $full_address = $address_type . ': ' . $address;
    if (!empty($address2)) {
        $full_address .= ', ' . $address2;
    }
    
    // Basic validation
    if (empty($name) || empty($phone) || empty($email) || empty($address)) {
        $warning_msg[] = 'Please fill all required fields!';
    } elseif (empty($payment_method)) {
        $warning_msg[] = 'Please select a payment method!';
    } else {
        try {
            // Generate order ID - using a unique identifier 
            $order_id = uniqid();
            $date = date('Y-m-d');
            
            if (isset($_GET['get_id'])) {
                // For direct product checkout
                $product_id = $_GET['get_id'];
                
                // Get product details again
                $get_product = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
                $get_product->execute([$product_id]);
                $fetch_product = $get_product->fetch(PDO::FETCH_ASSOC);
                
                // Insert order - disesuaikan dengan struktur database yang ada
                $insert_order = $conn->prepare("INSERT INTO orders (id, user_id, name, number, email, address, method, product_id, price, qty, dates, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insert_order->execute([
                    $order_id,               // id
                    $user_id,                // user_id
                    $name,                   // name
                    $phone,                  // number
                    $email,                  // email
                    $full_address,           // address (dengan address_type)
                    $payment_method,         // method
                    $product_id,             // product_id
                    $fetch_product['price'], // price
                    1,                       // qty
                    $date,                   // dates
                    'pending'                // status
                ]);
                
                // Redirect to order confirmation with success message
                $success_msg[] = 'Order placed successfully!';
                header("location:../admin_panel/home.php");
                
            } else {
                // For cart checkout - process each item in cart
                $check_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
                $check_cart->execute([$user_id]);
                
                if ($check_cart->rowCount() > 0) {
                    while ($fetch_cart = $check_cart->fetch(PDO::FETCH_ASSOC)) {
                        // Insert each cart item as an order
                        $insert_order = $conn->prepare("INSERT INTO orders (id, user_id, name, number, email, address, method, product_id, price, qty, dates, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $insert_order->execute([
                            uniqid(),                // id - unique for each order item
                            $user_id,                // user_id
                            $name,                   // name
                            $phone,                  // number
                            $email,                  // email
                            $full_address,           // address (dengan address_type)
                            $payment_method,         // method
                            $fetch_cart['product_id'], // product_id
                            $fetch_cart['price'],    // price
                            $fetch_cart['qty'],      // qty
                            $date,                   // dates
                            'pending'                // status
                        ]);
                    }
                    
                    // Empty the cart after successful orders
                    $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                    $delete_cart->execute([$user_id]);
                    
                    // Redirect to order confirmation with success message
                    $success_msg[] = 'All items ordered successfully!';
                    header('refresh:2;url=../admin_panel/view1_product.php');
                }
            }
        } catch (PDOException $e) {
            $warning_msg[] = 'Database Error: ' . $e->getMessage();
            // Untuk debugging, tampilkan error lengkap
            error_log("Database Error in checkout: " . $e->getMessage());
        }
    }
}

// Get user information if available
$user_info = [];
$get_user = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$get_user->execute([$user_id]);
if ($get_user->rowCount() > 0) {
    $user_info = $get_user->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Gelato Ice</title>
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

        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 992px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }

        .checkout-box {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 20px;
        }

        h1 {
            text-align: center;
            padding: 20px 0;
            color: var(--purple);
            font-size: 2rem;
            margin-bottom: 20px;
        }

        h2 {
            color: var(--purple);
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .total-box {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 20px;
        }

        .total-amount {
            font-size: 1.8rem;
            text-align: right;
            margin-top: 10px;
            font-weight: bold;
            color: var(--purple);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .form-group label span {
            color: #ff0000;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 576px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .payment-methods {
            margin-top: 20px;
        }

        .payment-method {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .payment-method:hover {
            background-color: #f9f9f9;
        }

        .payment-method input {
            margin-right: 10px;
        }

        .btn-place-order {
            background-color: var(--purple);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            display: block;
            width: 100%;
            margin-top: 20px;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-place-order:hover {
            background-color: var(--dark-purple);
            transform: translateY(-2px);
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

        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; }
        }

        .order-summary {
            margin-bottom: 20px;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .item-price {
            color: var(--purple);
        }

        .item-quantity {
            color: #777;
            font-size: 0.9rem;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #eee;
        }

        .section-title {
            color: #777;
            font-size: 1rem;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .required-star {
            color: red;
        }

        .payment-success-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .payment-success-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.5s;
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .success-icon {
            font-size: 5rem;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .payment-success-content h2 {
            color: #4CAF50;
            margin-bottom: 15px;
            border-bottom: none;
        }
        
        .loading-spinner {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
        
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 4px solid #704264;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                <a href="../admin_panel/cart.php">
                    <i class='bx bx-cart'></i>
                    Cart
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
                foreach($success_msg as $msg){
                    echo '<div class="message success"><span>'.$msg.'</span></div>';
                }
            }
            if(isset($warning_msg)){
                foreach($warning_msg as $msg){
                    echo '<div class="message warning"><span>'.$msg.'</span></div>';
                }
            }
            ?>
        </div>

        <h1>Checkout</h1>

        <!-- Display total amount -->
        <div class="total-box">
            <div class="section-title">Total Amount Payable:</div>
            <div class="total-amount">
                <?php
                if (isset($_GET['get_id'])) {
                    $select_product = $conn->prepare("SELECT * FROM products WHERE id = ?");
                    $select_product->execute([$_GET['get_id']]);
                    $fetch_product = $select_product->fetch(PDO::FETCH_ASSOC);
                    $grand_total = $fetch_product['price'];
                    echo 'Rp ' . number_format($grand_total) . '/-';
                } else {
                    $select_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
                    $select_cart->execute([$user_id]);
                    $grand_total = 0;
                    while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                        $sub_total = $fetch_cart['price'] * $fetch_cart['qty'];
                        $grand_total += $sub_total;
                    }
                    echo 'Rp ' . number_format($grand_total) . '/-';
                }
                ?>
            </div>
        </div>

        <div class="checkout-container">
            <!-- Left Column: Order Summary -->
            <div>
                <div class="checkout-box">
                    <h2>My Bag</h2>
                    <div class="order-summary">
                        <?php
                        if (isset($_GET['get_id'])) {
                            // Show single product
                            $select_product = $conn->prepare("SELECT * FROM products WHERE id = ?");
                            $select_product->execute([$_GET['get_id']]);
                            $fetch_product = $select_product->fetch(PDO::FETCH_ASSOC);
                        ?>
                            <div class="order-item">
                                <img src="../image/<?= $fetch_product['image']; ?>" alt="<?= $fetch_product['name']; ?>">
                                <div class="item-info">
                                    <div class="item-name"><?= $fetch_product['name']; ?></div>
                                    <div class="item-price">Rp <?= number_format($fetch_product['price']); ?>/-</div>
                                    <span class="item-quantity">Quantity: 1</span>
                                </div>
                            </div>
                        <?php
                        } else {
                            // Show all cart items
                            $select_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
                            $select_cart->execute([$user_id]);
                            
                            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                                $select_products = $conn->prepare("SELECT * FROM products WHERE id = ?");
                                $select_products->execute([$fetch_cart['product_id']]);
                                $fetch_product = $select_products->fetch(PDO::FETCH_ASSOC);
                                $sub_total = $fetch_cart['price'] * $fetch_cart['qty'];
                        ?>
                            <div class="order-item">
                                <img src="../image/<?= $fetch_product['image']; ?>" alt="<?= $fetch_product['name']; ?>">
                                <div class="item-info">
                                    <div class="item-name"><?= $fetch_product['name']; ?></div>
                                    <div class="item-price">Rp <?= number_format($fetch_cart['price']); ?>/-</div>
                                    <span class="item-quantity">Quantity: <?= $fetch_cart['qty']; ?></span>
                                </div>
                            </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Billing Details -->
            <div>
                <form action="" method="post">
                    <div class="checkout-box">
                        <h2>Billing Details</h2>
                        
                        <div class="form-group">
                            <label for="name">Your Name <span class="required-star">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Enter your name" value="<?= $user_info['name'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Your Number <span class="required-star">*</span></label>
                                <input type="text" id="phone" name="phone" class="form-control" placeholder="Enter your number" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Your Email <span class="required-star">*</span></label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" value="<?= $user_info['email'] ?? ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address_type">Address Type <span class="required-star">*</span></label>
                            <select id="address_type" name="address_type" class="form-control" required>
                                <option value="home" <?= $address_type == 'home' ? 'selected' : ''; ?>>Home</option>
                                <option value="office" <?= $address_type == 'office' ? 'selected' : ''; ?>>Office</option>
                                <option value="other" <?= $address_type == 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address Line <span class="required-star">*</span></label>
                            <input type="text" id="address" name="address" class="form-control" placeholder="e.g. street name" required>
                        </div>
                    </div>
                    
                    <div class="checkout-box">
                        <h2>Payment Method <span class="required-star">*</span></h2>
                        
                        <div class="payment-methods">
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="cash" <?= $payment_method == 'cash' ? 'checked' : ''; ?> required>
                                Cash 
                            </label>
                            
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="cash on delivery" <?= $payment_method == 'cash on delivery' ? 'checked' : ''; ?>>
                                Cash On Delivery
                            </label>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn-place-order">
                            <i class='bx bx-check-circle'></i> Place Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="payment-success-modal" id="successModal">
        <div class="payment-success-content">
            <div class="success-icon">
                <i class='bx bx-check-circle'></i>
            </div>
            <h2>Payment Successful!</h2>
            <p>Your order has been placed successfully.</p>
            <p>You will be redirected to your orders page shortly.</p>
            <div class="loading-spinner">
                <div class="spinner"></div>
            </div>
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

        // If there's a success message, show the success modal
        <?php if (!empty($success_msg)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const successModal = document.getElementById('successModal');
            successModal.style.display = 'flex';
        });
        <?php endif; ?>
    </script>
</body>
</html>