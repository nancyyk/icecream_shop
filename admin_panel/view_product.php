<?php 
include '../components/connect.php';
session_start();
$seller_id = $_SESSION['seller_id'] ?? '';

// Redirect jika belum login
if (!$seller_id) {
  header('location:seller_login.php');
  exit();
}

// Handle AJAX delete request
if (isset($_POST['action']) && $_POST['action'] == 'delete_product' && isset($_POST['product_id'])) {
  $product_id = $_POST['product_id'];
  
  // Verify the product belongs to this seller
  $verify_product = $conn->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
  $verify_product->execute([$product_id, $seller_id]);
  
  if ($verify_product->rowCount() > 0) {
    // Get image filename before deleting
    $fetch_image = $verify_product->fetch(PDO::FETCH_ASSOC);
    $image = $fetch_image['image'];
    
    // Delete product from database
    $delete_product = $conn->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
    $delete_product->execute([$product_id, $seller_id]);
    
    // Delete image file if it exists
    if (!empty($image) && file_exists('../image/' . $image)) {
      unlink('../image/' . $image);
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Produk berhasil dihapus']);
    exit();
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan atau tidak berhak menghapus produk ini']);
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Gelato Ice - My Product</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <style>
    body { margin: 0; font-family: 'Segoe UI', sans-serif; background-color: #f9f9f4; }
    .top-bar { background-color: #fff; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .logo-container { display: flex; align-items: center; }
    .logo-container img { height: 70px; margin-right: 20px; }
    .logo-text { font-size: 36px; font-weight: bold; color: #ff69b4; }
    .highlight { font-style: italic; font-weight: normal; font-size: 26px; color: #e91e63; }
    .user-icon { position: relative; cursor: pointer; }
    .user-icon i { font-size: 32px; color: #f48fb1; transition: 0.3s; }
    .user-icon i:hover { color: #e91e63; }
    .user-popup { display: none; position: absolute; right: 0; top: 50px; background: white; padding: 15px; border: 1px solid #ccc; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 10px; width: 220px; text-align: center; z-index: 10; }
    .user-popup img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 2px solid #ff69b4; margin-bottom: 10px; }
    .user-popup .name { font-weight: bold; color: #444; margin-bottom: 10px; font-size: 16px; }
    .user-popup .role { font-weight: bold; color: #666; margin-bottom: 15px; }
    .user-popup .buttons { display: flex; justify-content: space-between; }
    .user-popup .button { padding: 10px 0; width: 45%; text-align: center; border-radius: 20px; text-decoration: none; font-size: 14px; font-weight: bold; transition: 0.3s; }
    .user-popup .profile-btn, .user-popup .logout-btn { background-color: #f8bbd0; color: #c2185b; }
    .user-popup .button:hover { opacity: 0.9; }

    .main-layout { display: flex; flex-wrap: wrap; }
    .sidebar { width: 250px; background-color: #fff; border-right: 1px solid #e0e0e0; padding: 20px; min-height: 100vh; }
    .sidebar img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 10px auto; border: 2px solid #ff69b4; display: block; }
    .sidebar .name { text-align: center; font-weight: bold; margin-bottom: 20px; color: #444; }
    .menu-title { color: #e91e63; font-weight: bold; margin-bottom: 10px; }
    .menu a { display: block; padding: 10px 15px; margin-bottom: 10px; background-color: #f9f9f4; border: 1px solid #ccc; border-radius: 10px; text-decoration: none; color: #444; font-weight: bold; transition: 0.3s; font-size: 14px; }
    .menu a:hover { background-color: #f3f3f3; border-color: #e91e63; color: #e91e63; }

    .social-links { text-align: center; margin-top: 20px; }
    .social-links i { margin: 0 5px; font-size: 18px; color: #555; cursor: pointer; }

    .content { flex: 1; padding: 40px; }
    .heading { text-align: center; margin: 30px auto 10px; }
    .heading h1 { font-size: 24px; color: #000; text-transform: uppercase; }

    /* Alert Messages */
    .message {
      padding: 12px 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      text-align: center;
      font-weight: bold;
    }
    .success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .box-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 25px;
      margin-top: 30px;
    }

    .product-box {
      background-color: #fff;
      padding: 18px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      width: 280px;
      text-align: center;
      transition: transform 0.2s, box-shadow 0.2s;
      position: relative;
    }

    .product-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .product-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 15px;
      border: 1px solid #f0f0f0;
    }

    /* Product name style - PINK color */
    .product-name {
      font-size: 20px;
      color: #ff69b4; /* Bright pink color */
      margin-bottom: 8px;
      font-weight: bold;
    }

    /* Product price style - RED color */
    .product-price {
      font-size: 18px;
      color: #ff0000; /* Pure red color */
      margin-bottom: 10px;
      font-weight: bold;
    }

    .sale-price {
      font-size: 16px;
      color: #777;
      text-decoration: line-through;
      margin-bottom: 5px;
    }

    .status-badge {
      display: inline-block;
      padding: 5px 12px;
      border-radius: 15px;
      font-size: 12px;
      font-weight: bold;
      margin-bottom: 15px;
    }
    
    .status-active {
      background-color: #d4edda;
      color: #155724;
    }
    
    .status-inactive {
      background-color: #f8d7da;
      color: #721c24;
    }

    .btn-container {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 10px;
    }

    .btn {
      display: inline-block;
      padding: 8px 18px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 14px;
      font-weight: bold;
      transition: background-color 0.3s, color 0.3s;
      cursor: pointer;
      border: none;
    }

    .btn-edit {
      background-color: #f8bbd0;
      color: #c2185b;
    }

    .btn-delete {
      background-color: #ffcdd2;
      color: #c62828;
    }

    .btn:hover {
      opacity: 0.9;
    }

    .no-products {
      text-align: center;
      padding: 30px;
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      margin: 30px auto;
      max-width: 500px;
    }

    .no-products p {
      color: #555;
      margin-bottom: 20px;
      font-size: 16px;
    }

    .no-products .btn {
      background-color: #f8bbd0;
      color: #c2185b;
      padding: 10px 25px;
    }

    /* Loading spinner */
    .spinner {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(255,255,255,.3);
      border-radius: 50%;
      border-top-color: #c62828;
      animation: spin 1s ease-in-out infinite;
      margin-right: 5px;
      vertical-align: middle;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    /* Notification popup */
    .notification {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 15px 25px;
      border-radius: 8px;
      color: white;
      font-weight: bold;
      z-index: 1000;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      transform: translateY(-100px);
      transition: transform 0.3s ease;
    }
    
    .notification.show {
      transform: translateY(0);
    }
    
    .notification.success {
      background-color: #4CAF50;
    }
    
    .notification.error {
      background-color: #F44336;
    }

    @media (max-width: 768px) {
      .main-layout { flex-direction: column; }
      .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ddd; }
      .product-box { width: 100%; }
      .content { padding: 20px; }
    }
  </style>
</head>
<body>

<?php 
$profile_img = 'default_avatar.png';
$seller_name = 'Guest';
if ($seller_id) {
  $select_profile = $conn->prepare("SELECT * FROM sellers WHERE id = ?");
  $select_profile->execute([$seller_id]);
  if ($select_profile->rowCount() > 0) {
    $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
    $profile_img = !empty($fetch_profile['image']) ? $fetch_profile['image'] : 'default_avatar.png';
    $seller_name = htmlspecialchars($fetch_profile['name']);
  }
}
?>

<!-- Notification div -->
<div id="notification" class="notification"></div>

<!-- TOP BAR -->
<div class="top-bar">
  <div class="logo-container">
    <img src="../image/logo.jpeg" alt="Logo">
    <div class="logo-text">G̾e̾l̾a̾t̾o̾ <span class="highlight">𝑖𝑐𝑒</span></div>
  </div>
  <div class="user-icon">
    <i class='bx bxs-user' onclick="toggleUserPopup()"></i>
    <div class="user-popup" id="userPopup">
      <img src="../uploaded_files/<?= $profile_img; ?>" alt="Profile">
      <div class="name"><?= $seller_name; ?></div>
      <div class="role">Admin</div>
      <div class="buttons">
        <a href="../components/update.php" class="button profile-btn">Profile</a>
        <a href="../admin_panel/home.php" onclick="return confirm('Logout dari website ini?');" class="button logout-btn">Logout</a>
      </div>
    </div>
  </div>
</div>

<!-- MAIN -->
<div class="main-layout">
  <!-- SIDEBAR -->
  <div class="sidebar">
    <img src="../uploaded_files/<?= $profile_img; ?>" alt="Profile">
    <div class="name"><?= $seller_name; ?></div>
    <div class="menu-title">MENU</div>
    <div class="menu">
      <a href="dashboard.php">DASHBOARD</a>
      <a href="add_products.php">ADD PRODUCTS</a>
      <a href="view_product.php">VIEW PRODUCT</a>
      <a href="../admin_panel/home.php" onclick="return confirm('Logout from this website?');">LOGOUT</a>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="content">
    <!-- Display success or error messages -->
    <?php if (isset($_GET['success'])) { ?>
      <div class="message success"><?= htmlspecialchars($_GET['success']); ?></div>
    <?php } ?>
    
    <?php if (isset($_GET['error'])) { ?>
      <div class="message error"><?= htmlspecialchars($_GET['error']); ?></div>
    <?php } ?>
    
    <section class="show-products">
      <div class="heading">
        <h1>YOUR PRODUCT</h1>
      </div>
      
      <!-- Product container -->
      <div class="box-container" id="productContainer">
        <?php
        $select_products = $conn->prepare("SELECT * FROM products WHERE seller_id = ?");
        $select_products->execute([$seller_id]);
        
        if ($select_products->rowCount() > 0) {
          while ($product = $select_products->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="product-box" id="product-<?= $product['id']; ?>">
          <?php if (!empty($product['image']) && file_exists('../image/' . $product['image'])) : ?>
            <img src="../image/<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="product-image">
          <?php else : ?>
            <div style="height:200px; display:flex; align-items:center; justify-content:center; border:1px dashed #ccc; border-radius:10px;">
              <span style="color:#888;">Tidak ada gambar</span>
            </div>
          <?php endif; ?>

          <div class="product-name"><?= htmlspecialchars($product['name']); ?></div>

          <?php if ($product['price'] !== null): ?>
            <div class="product-price">IDR <?= number_format($product['price'], 0, ',', '.'); ?></div>
          <?php endif; ?>

          <?php if ($product['stock'] !== null): ?>
            <div style="color:#555; font-size:14px;">Stock: <?= htmlspecialchars($product['stock']); ?></div>
          <?php endif; ?>

          <div class="status-badge <?= $product['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
            <?= htmlspecialchars(ucfirst($product['status'])); ?>
          </div>
          
          <div class="btn-container">
            <a href="../components/edit.php?id=<?= $product['id']; ?>" class="btn btn-edit">
  <i class='bx bx-edit'></i> Edit
</a>

            <button class="btn btn-delete" onclick="deleteProduct(<?= $product['id']; ?>)">
              <i class='bx bx-trash'></i> Delete
            </button>
          </div>
        </div>
        <?php } } else { ?>
          <div class="no-products" id="noProductsMessage">
            <p>No products added yet!</p>
            <a href="add_products.php" class="btn">Add New Product</a>
          </div>
        <?php } ?>
      </div>
    </section>
  </div>
</div>

<script>
function toggleUserPopup() {
  const popup = document.getElementById('userPopup');
  popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
}

window.addEventListener('click', function(e) {
  const popup = document.getElementById('userPopup');
  const icon = document.querySelector('.user-icon i');
  if (!popup.contains(e.target) && e.target !== icon) {
    popup.style.display = 'none';
  }
});

// Function to show notification
function showNotification(message, type) {
  const notification = document.getElementById('notification');
  notification.textContent = message;
  notification.className = `notification ${type}`;
  notification.classList.add('show');
  
  setTimeout(() => {
    notification.classList.remove('show');
  }, 3000);
}

// Function to delete product with AJAX
function deleteProduct(productId) {
  if (!confirm('Hapus produk ini?')) {
    return;
  }
  
  // Find the delete button and add loading spinner
  const deleteBtn = event.currentTarget;
  const originalContent = deleteBtn.innerHTML;
  deleteBtn.innerHTML = '<div class="spinner"></div> Menghapus...';
  deleteBtn.disabled = true;
  
  // Create form data
  const formData = new FormData();
  formData.append('action', 'delete_product');
  formData.append('product_id', productId);
  
  // Send AJAX request
  fetch('view_product.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      // Remove product box from DOM
      const productBox = document.getElementById(`product-${productId}`);
      productBox.style.opacity = '0';
      productBox.style.transform = 'scale(0.8)';
      productBox.style.transition = 'all 0.3s ease';
      
      setTimeout(() => {
        productBox.remove();
        
        // Check if there are any products left
        const productContainer = document.getElementById('productContainer');
        if (productContainer.children.length === 0) {
          // Create "no products" message if all products are deleted
          const noProducts = document.createElement('div');
          noProducts.className = 'no-products';
          noProducts.id = 'noProductsMessage';
          noProducts.innerHTML = `
            <p>Belum ada produk yang ditambahkan!</p>
            <a href="add_products.php" class="btn">Tambah Produk Baru</a>
          `;
          productContainer.appendChild(noProducts);
        }
        
        showNotification('Produk berhasil dihapus', 'success');
      }, 300);
    } else {
      // Restore delete button
      deleteBtn.innerHTML = originalContent;
      deleteBtn.disabled = false;
      
      showNotification(data.message || 'Gagal menghapus produk', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    // Restore delete button
    deleteBtn.innerHTML = originalContent;
    deleteBtn.disabled = false;
    
    showNotification('Terjadi kesalahan saat menghapus produk', 'error');
  });
}

// Auto close alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(function() {
    const messages = document.querySelectorAll('.message');
    messages.forEach(function(message) {
      message.style.display = 'none';
    });
  }, 5000);
});
</script>

</body>
</html>