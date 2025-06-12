<?php
include '../components/connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;

// Ambil data produk berdasarkan ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);

    if ($stmt->rowCount() == 1) {
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Product not found.";
    }
} else {
    $error = "Invalid product ID.";
}

// Hitung jumlah item di cart
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
  <title>View Product</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f2f2f2;
      padding: 40px;
    }
    .product-box {
      background: white;
      max-width: 700px;
      margin: 0 auto;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      padding: 20px;
    }
    .product-box img {
      max-width: 100%;
      height: auto;
      border-radius: 10px;
    }
    .product-box h2 {
      color: #704264;
      margin-top: 20px;
    }
    .product-box .price {
      font-size: 1.5rem;
      color: #333;
      margin: 10px 0;
    }
    .product-box p {
      line-height: 1.6;
      color: #555;
    }
    .btn {
      display: inline-block;
      margin-top: 20px;
      background-color: #704264;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 6px;
    }
    .btn:hover {
      background-color: #5a3350;
    }
    .back-link {
      display: inline-block;
      margin-bottom: 20px;
      color: #704264;
      text-decoration: none;
    }
    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<?php if (isset($error)): ?>
  <p><?= $error ?></p>
<?php else: ?>
  <a href="catalog.php" class="back-link">&larr; Back to Catalog</a>
  <div class="product-box">
    <img src="../uploaded_img/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
    <h2><?= htmlspecialchars($product['name']) ?></h2>
    <div class="price">Rp<?= number_format($product['price'], 0, ',', '.') ?></div>
    <p><?= nl2br(htmlspecialchars($product['details'])) ?></p>

    <?php if ($user_id): ?>
      <form method="post" action="catalog.php">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <input type="number" name="qty" min="1" max="99" value="1" style="width:60px;">
        <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
      </form>
    <?php else: ?>
      <a href="../admin_panel/login_universal.php" class="btn">Login to Order</a>
    <?php endif; ?>
  </div>
<?php endif; ?>

</body>
</html>
