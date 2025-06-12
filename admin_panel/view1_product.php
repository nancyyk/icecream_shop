<?php
include '../components/connect.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// DEBUG: Debugging can be enabled/disabled here
$debug_login = false; // Set to true only for development

// Handle logout functionality
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to the same page after logout
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Check if user is logged in - PROPERLY
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    if($debug_login) {
        echo "<div style='background-color: green; color: white; padding: 10px;'>
            Debug: Logged in as user_id: {$user_id}
        </div>";
    }
} else {
    $user_id = null; // Ensure user_id is null when not logged in
}

// Handle Buy Now functionality
if (isset($_POST['buy_now'])) {
    // Verify user is logged in before proceeding
    if (!$user_id) {
        // Store current page URL in session to redirect back after login
        $_SESSION['redirect_after_login'] = $_SERVER['PHP_SELF'];
        
        // Redirect to login if not logged in
        header('Location: ../admin_panel/login1.php');
        exit;
    } else {
        $product_id = $_POST['product_id'];
        
        // Proper validation for quantity - ensure it's a positive integer
        $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
        if ($qty < 1 || $qty > 99) {
            $qty = 1; // Default to 1 if invalid quantity provided
        }

        // Check if product exists
        $select_product = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
        $select_product->execute([$product_id]);
        $product_data = $select_product->fetch(PDO::FETCH_ASSOC);

        if ($product_data) {
            $id = unique_id();
            
            // Check if product already exists in cart
            $verify_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
            $verify_cart->execute([$user_id, $product_id]);
            
            if ($verify_cart->rowCount() > 0) {
                // Update quantity if product already in cart
                $update_cart = $conn->prepare("UPDATE cart SET qty = qty + ? WHERE user_id = ? AND product_id = ?");
                $update_cart->execute([$qty, $user_id, $product_id]);
            } else {
                // Add new product to cart
                $insert_cart = $conn->prepare("INSERT INTO cart(id, user_id, product_id, price, qty) VALUES(?, ?, ?, ?, ?)");
                $insert_cart->execute([$id, $user_id, $product_id, $product_data['price'], $qty]);
            }
            
            // Redirect to checkout page
            header('Location: ../components/checkout.php');
            exit;
            
        } else {
            $warning_msg[] = 'Product not found';
        }
    }
}

// Adding products to cart
if (isset($_POST['add_to_cart'])) {
    // Verify user is logged in before adding to cart
    if (!$user_id) {
        // Store current page URL in session to redirect back after login
        $_SESSION['redirect_after_login'] = $_SERVER['PHP_SELF'];
        
        // Redirect to login if not logged in
        header('Location: ../admin_panel/login1.php');
        exit;
    } else {
        $id = unique_id();
        $product_id = $_POST['product_id'];
        
        // Proper validation for quantity - ensure it's a positive integer
        $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
        if ($qty < 1 || $qty > 99) {
            $qty = 1; // Default to 1 if invalid quantity provided
        }

        $verify_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $verify_cart->execute([$user_id, $product_id]);

        $max_cart_items = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
        $max_cart_items->execute([$user_id]);

        if ($verify_cart->rowCount() > 0) {
            $warning_msg[] = 'Product already exists in your cart';
        } elseif ($max_cart_items->rowCount() > 20) {
            $warning_msg[] = 'Cart is full (maximum 20 items)';
        } else {
            $select_price = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
            $select_price->execute([$product_id]);
            $fetch_price = $select_price->fetch(PDO::FETCH_ASSOC);

            if ($fetch_price) {
                $insert_cart = $conn->prepare("INSERT INTO cart(id, user_id, product_id, price, qty) VALUES(?, ?, ?, ?, ?)");
                $insert_cart->execute([$id, $user_id, $product_id, $fetch_price['price'], $qty]);
                $success_msg[] = 'Product added to cart successfully';
            } else {
                $warning_msg[] = 'Product not found';
            }
        }
    }
}

// Process search query
$search_term = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    $search_term = filter_var($search_term, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $select_products = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
    $select_products->execute(["%$search_term%"]);
} else {
    $select_products = $conn->prepare("SELECT * FROM products");
    $select_products->execute();
}

// Get cart count
$cart_count = 0;
if ($user_id) {
    $get_cart = $conn->prepare("SELECT SUM(qty) AS total FROM cart WHERE user_id = ?");
    $get_cart->execute([$user_id]);
    $cart_data = $get_cart->fetch(PDO::FETCH_ASSOC);
    $cart_count = $cart_data['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gelato Ice - Catalog</title>
  <!-- Load Boxicons with integrity check -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <!-- Fallback for font awesome icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --blue: #704264;
      --dark-blue: #5a3350;
      --purple: #704264;
      --box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Arial', sans-serif;
    }

    body {
      background-color: rgb(0, 0, 0);
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .produk-box {
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: var(--box-shadow);
      margin-top: 80px; /* Add space for the fixed header elements */
    }

    h1 {
      text-align: center;
      padding: 20px 0;
      color: var(--dark-blue);
      font-size: 2.5rem;
    }

    .products .box-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      justify-content: center;
    }

    .products .box-container .box {
      background-color: white;
      box-shadow: var(--box-shadow);
      border-radius: 10px;
      padding: 20px;
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: transform 0.3s ease;
    }

    .products .box-container .box:hover {
      transform: translateY(-5px);
      background-color: #f9f0fc;
    }

    .products .box-container .box .img {
      width: 80%;
      height: 200px;
      object-fit: contain;
      margin-bottom: 15px;
    }
    
    .products .box-container .box .name {
      font-size: 1.2rem;
      color: #333;
      text-transform: capitalize;
      margin: 10px 0;
      text-align: center;
    }

    .products .box-container .box .actions {
      display: flex;
      gap: 10px;
      margin: 15px 0;
    }

    .products .box-container .box .actions button,
    .products .box-container .box .actions a {
      background-color: var(--blue);
      color: white;
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .products .box-container .box .actions button:hover,
    .products .box-container .box .actions a:hover {
      background-color: var(--dark-blue);
    }

    .products .box-container .box .flex {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      margin: 10px 0;
    }

    .products .box-container .box .price {
      font-size: 1.2rem;
      color: var(--blue);
      font-weight: bold;
    }

    .products .box-container .box .qty {
      border: 1px solid #ddd;
      border-radius: 5px;
      padding: 5px;
      width: 60px;
      text-align: center;
    }

    .btn {
      display: inline-block;
      background-color: var(--blue);
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 20px;
      text-align: center;
      margin-top: 10px;
      font-weight: bold;
      transition: background-color 0.3s ease;
      cursor: pointer;
      border: none;
    }

    .btn:hover {
      background-color: var(--dark-blue);
    }

    .empty {
      text-align: center;
      font-size: 1.5rem;
      color: #777;
      padding: 20px;
    }

    /* Messages */
    .message-container {
      position: fixed;
      top: 20px;
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

    /* Fixed positioning for UI elements */
    .header-ui {
      position: fixed;
      top: 20px;
      right: 20px;
      display: flex;
      gap: 10px;
      z-index: 1000;
    }

    .login-box,
    .logout-box {
      background-color: var(--purple);
      padding: 10px 20px;
      border-radius: 5px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .login-box a,
    .logout-box a {
      color: white;
      text-decoration: none;
      font-weight: bold;
      font-size: 1rem;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .login-box a:hover,
    .logout-box a:hover {
      text-decoration: underline;
    }
    
    .cart-icon {
      background-color: var(--purple);
      width: 45px;
      height: 45px;
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .cart-icon a {
      color: white;
      text-decoration: none;
      font-size: 1.5rem;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .cart-count {
      position: absolute;
      top: -5px;
      right: -5px;
      background-color: black;
      color: white;
      font-size: 0.8rem;
      font-weight: bold;
      border-radius: 50%;
      padding: 2px 6px;
      min-width: 18px;
      text-align: center;
    }

    /* Search Container */
    .search-container {
      position: sticky;
      top: 80px;
      background-color: white;
      z-index: 999;
      padding: 15px 20px;
      border-radius: 10px 10px 0 0;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: -10px;
    }

    /* Search Form */
    .search-form {
      display: flex;
      justify-content: center;
      align-items: center;
      max-width: 600px;
      margin: 0 auto;
    }

    /* Search Input */
    .search-input {
      flex: 1;
      padding: 10px 15px;
      border: 2px solid var(--blue);
      border-radius: 25px 0 0 25px;
      font-size: 16px;
      outline: none;
    }

    /* On Focus */
    .search-input:focus {
      border-color: var(--dark-blue);
    }

    /* Search Button */
    .search-btn {
      background-color: var(--blue);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 0 25px 25px 0;
      cursor: pointer;
      font-size: 16px;
      display: flex;
      align-items: center;
    }

    .search-btn:hover {
      background-color: var(--dark-blue);
    }

    .search-btn i {
      margin-right: 5px;
    }

    /* Icon fallback */
    .icon {
      display: inline-block;
      width: 20px;
      height: 20px;
      margin-right: 5px;
    }
    
    .icon-logout {
      background-color: white;
      clip-path: polygon(40% 0%, 40% 40%, 100% 40%, 100% 60%, 40% 60%, 40% 100%, 20% 100%, 20% 60%, 0% 60%, 0% 40%, 20% 40%, 20% 0%);
      transform: rotate(180deg);
    }
    
    .icon-login {
      background-color: white;
      clip-path: polygon(40% 0%, 40% 40%, 100% 40%, 100% 60%, 40% 60%, 40% 100%, 20% 100%, 20% 60%, 0% 60%, 0% 40%, 20% 40%, 20% 0%);
      transform: rotate(0deg);
    }
    
    .icon-cart {
      background-color: white;
      border-radius: 5px;
      position: relative;
    }
    
    .icon-cart::before {
      content: "";
      position: absolute;
      top: -8px;
      left: 5px;
      width: 10px;
      height: 8px;
      background-color: white;
      border-radius: 10px 10px 0 0;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
      .products .box-container {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      }
      
      .header-ui {
        flex-direction: column;
        align-items: flex-end;
      }
      
      .produk-box {
        margin-top: 150px;
      }
    }
    
    .home-box {
      display: inline-block;
      background-color:rgb(86, 147, 57); /* ungu gelap */
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      margin-right: 10px;
      transition: background-color 0.3s ease;
    }

    .home-box:hover {
      background-color: pink;
    }

  </style>
</head>
<body>
  <!-- Header UI with fixed positioning -->
  <div class="header-ui">
    <!-- Cart Icon - Show if logged in -->
    <?php if($user_id): ?>
      <div class="cart-icon">
        <a href="../admin_panel/cart.php" title="Shopping Cart">
          <i class='bx bx-cart'></i>
          <i class="fas fa-shopping-cart"></i>
          <span class="cart-count"><?= $cart_count ?></span>
        </a>
      </div>
    <?php endif; ?>
    
    <!-- Login/Logout Box -->
    <?php if($user_id): ?>
      <div class="logout-box">
        <a href="?logout=true" onclick="return confirm('Logout from this website?');">
          <i class='bx bx-log-out'></i>
          <i class="fas fa-sign-out-alt"></i>
          LOGOUT
        </a>
      </div>
    <?php else: ?>
      <a href="../admin_panel/home.php" class="home-box">
        🏠 Home
      </a>
      <div class="login-box">
        <a href="../admin_panel/login_universal.php">
          <i class='bx bx-log-in'></i>
          <i class="fas fa-sign-in-alt"></i>
          LOGIN
        </a>
      </div>
    <?php endif; ?>
  </div>

  <div class="produk">
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

      <div class="search-container">
        <form class="search-form" action="view1_product.php" method="GET">
          <input type="text" name="search" class="search-input" placeholder="Search products..." value="<?= htmlspecialchars($search_term) ?>">
          <button type="submit" class="search-btn">
            <i class="bx bx-search"></i>
            <i class="fas fa-search"></i>
            Search
          </button>
        </form>
      </div>

      <div class="produk-box">
        <h1>
          <?php if(!empty($search_term)): ?>
            Results for: <?= htmlspecialchars($search_term) ?>
          <?php else: ?>
            Catalog Menu
          <?php endif; ?>
        </h1>
        
        <section class="products">
          <div class="box-container">
            <?php 
            if ($select_products->rowCount() > 0) {
              while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
            ?>
              <form action="" method="post" class="box">
                <img src="../image/<?= htmlspecialchars($fetch_products['image']); ?>" class="img" alt="<?= htmlspecialchars($fetch_products['name']); ?>">
                <h3 class="name"><?= htmlspecialchars($fetch_products['name']); ?></h3>
                <div class="actions">
                  <?php if($user_id): ?>
                  <button type="submit" name="add_to_cart" title="Add to Cart">
                    <i class='bx bx-cart'></i>
                    <i class="fas fa-cart-plus"></i>
                  </button>
                  <?php endif; ?>
                </div>
                <input type="hidden" name="product_id" value="<?= $fetch_products['id']; ?>">
                <div class="flex">
                  <p class="price">IDR <?= number_format($fetch_products['price']); ?>/-</p>
                  <input type="number" name="qty" required min="1" value="1" max="99" maxlength="2" class="qty">
                </div>
                <?php if($user_id): ?>
                  <button type="submit" name="buy_now" class="btn">Buy Now</button>
                <?php else: ?>
                  <a href="../admin_panel/login_universal.php" class="btn">Login to Buy</a>
                <?php endif; ?>
              </form>
            <?php
              }
            } else {
              echo '<p class="empty">No products found' . (!empty($search_term) ? ' matching "' . htmlspecialchars($search_term) . '"' : '') . '!</p>';
            }
            ?>
          </div>
        </section>
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

    // Check if Boxicons is loaded properly and use fallback if needed
    document.addEventListener('DOMContentLoaded', function() {
      const boxIconsLoaded = Array.from(document.styleSheets).some(sheet => 
        sheet.href && (sheet.href.includes('boxicons') || sheet.href.includes('unpkg.com/boxicons'))
      );
      
      if (!boxIconsLoaded) {
        console.warn('BoxIcons CSS is not loaded, using Font Awesome fallback');
        // Hide all boxicons
        document.querySelectorAll('.bx').forEach(el => el.style.display = 'none');
        // Show all font awesome icons
        document.querySelectorAll('.fas').forEach(el => el.style.display = 'inline-block');
      } else {
        // Hide all font awesome icons
        document.querySelectorAll('.fas').forEach(el => el.style.display = 'none');
      }
    });
  </script>
</body>
</html>