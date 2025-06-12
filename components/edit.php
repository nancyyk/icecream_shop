<?php 
include '../components/connect.php';
session_start();
$seller_id = $_SESSION['seller_id'] ?? '';

// Redirect jika belum login
if (!$seller_id) {
  header('location:seller_login.php');
  exit();
}

// Get product ID from URL
$product_id = $_GET['id'] ?? '';
if (!$product_id) {
  header('location:view_product.php?error=ID produk tidak ditemukan');
  exit();
}

// Fetch product data
$select_product = $conn->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$select_product->execute([$product_id, $seller_id]);

if ($select_product->rowCount() == 0) {
  header('location:view_product.php?error=Produk tidak ditemukan atau tidak berhak mengedit');
  exit();
}

$product = $select_product->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if (isset($_POST['update_product'])) {
  $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
  $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
  $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);
  $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);

  
  $image = $product['image']; // Keep existing image by default
  $image_folder = '../image/';
  
  // Handle image upload if new image is provided
  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_name = $_FILES['image']['name'];
    $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    
    // Validate image
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($image_extension, $allowed_extensions)) {
      if ($image_size <= 2000000) { // 2MB limit
        // Delete old image if exists
        if (!empty($product['image']) && file_exists($image_folder . $product['image'])) {
          unlink($image_folder . $product['image']);
        }
        
        // Generate unique filename
        $image = uniqid() . '.' . $image_extension;
        
        if (!move_uploaded_file($image_tmp_name, $image_folder . $image)) {
          $warning_msg[] = 'Gagal mengupload gambar baru, menggunakan gambar lama';
          $image = $product['image'];
        }
      } else {
        $warning_msg[] = 'Ukuran gambar terlalu besar (maksimal 2MB)';
      }
    } else {
      $warning_msg[] = 'Format gambar tidak didukung (hanya JPG, JPEG, PNG, WEBP)';
    }
  }
  
  // Update product in database
  if (!empty($name) && !empty($price) && !empty($stock) && !empty($status)) {
    $update_product = $conn->prepare("UPDATE products SET name = ?, price = ?, stock = ?, status = ?, image = ? WHERE id = ? AND seller_id = ?");
    
if ($update_product->execute([$name, $price, $stock, $status, $image, $product_id, $seller_id])) {
      header('location:../admin_panel/view_product.php?success=Produk berhasil diperbarui');
      exit();
    } else {
      $warning_msg[] = 'Gagal memperbarui produk';
    }
  } else {
    $warning_msg[] = 'Semua field wajib diisi';
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Gelato Ice - Edit Product</title>
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

    .content { flex: 1; padding: 40px; }
    .heading { text-align: center; margin: 30px auto 10px; }
    .heading h1 { font-size: 24px; color: #000; text-transform: uppercase; }

    .message {
      padding: 12px 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      text-align: center;
      font-weight: bold;
    }
    .warning {
      background-color: #fff3cd;
      color: #856404;
      border: 1px solid #ffeaa7;
    }

    .form-container {
      max-width: 800px;
      margin: 0 auto;
      background-color: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .form-row {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }

    .form-group {
      flex: 1;
    }

    .form-group.full-width {
      flex: 100%;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #444;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 12px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 14px;
      transition: border-color 0.3s;
      box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #ff69b4;
    }

    .form-group textarea {
      resize: vertical;
      min-height: 100px;
    }

    .current-image {
      margin-bottom: 15px;
      text-align: center;
    }

    .current-image img {
      max-width: 200px;
      max-height: 200px;
      border-radius: 8px;
      border: 2px solid #f0f0f0;
    }

    .current-image p {
      margin-top: 10px;
      color: #666;
      font-size: 14px;
    }

    .button-group {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 30px;
    }

    .btn {
      display: inline-block;
      padding: 12px 30px;
      border-radius: 25px;
      text-decoration: none;
      font-size: 16px;
      font-weight: bold;
      transition: all 0.3s;
      cursor: pointer;
      border: none;
      text-align: center;
    }

    .btn-primary {
      background-color: #ff69b4;
      color: white;
    }

    .btn-primary:hover {
      background-color: #e91e63;
      transform: translateY(-2px);
    }

    .btn-secondary {
      background-color: #f8bbd0;
      color: #c2185b;
    }

    .btn-secondary:hover {
      background-color: #f48fb1;
      transform: translateY(-2px);
    }

    .preview-image {
      max-width: 200px;
      max-height: 200px;
      margin-top: 10px;
      border-radius: 8px;
      border: 2px solid #f0f0f0;
      display: none;
    }

    @media (max-width: 768px) {
      .main-layout { flex-direction: column; }
      .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ddd; }
      .content { padding: 20px; }
      .form-row { flex-direction: column; }
      .button-group { flex-direction: column; align-items: center; }
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
    <section class="edit-product">
      <div class="heading">
        <h1>EDIT PRODUCT</h1>
      </div>
      
      <!-- Display warning messages -->
      <?php if (isset($warning_msg)) { ?>
        <?php foreach ($warning_msg as $msg) { ?>
          <div class="message warning"><?= htmlspecialchars($msg); ?></div>
        <?php } ?>
      <?php } ?>
      
      <div class="form-container">
        <form action="" method="post" enctype="multipart/form-data">
          <!-- Product Name and Price -->
          <div class="form-row">
            <div class="form-group">
              <label for="name">Nama Produk <span style="color: red;">*</span></label>
              <input type="text" name="name" id="name" value="<?= htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="form-group">
              <label for="price">Harga (IDR) <span style="color: red;">*</span></label>
              <input type="number" name="price" id="price" min="0" step="0.01" value="<?= $product['price']; ?>" required>
            </div>
          </div>

          <!-- Stock and Status -->
          <div class="form-row">
            <div class="form-group">
              <label for="stock">Stok <span style="color: red;">*</span></label>
              <input type="number" name="stock" id="stock" min="0" value="<?= $product['stock']; ?>" required>
            </div>
            <div class="form-group">
              <label for="status">Status <span style="color: red;">*</span></label>
              <select name="status" id="status" required>
                <option value="active" <?= $product['status'] == 'active' ? 'selected' : ''; ?>>Aktif</option>
                <option value="inactive" <?= $product['status'] == 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
              </select>
            </div>
          </div>

          <!-- Current Image -->
          <div class="form-row">
            <div class="form-group full-width">
              <label>Gambar Saat Ini</label>
              <div class="current-image">
                <?php if (!empty($product['image']) && file_exists('../image/' . $product['image'])) : ?>
                  <img src="../image/<?= htmlspecialchars($product['image']); ?>" alt="Current Product Image">
                  <p>Gambar saat ini: <?= htmlspecialchars($product['image']); ?></p>
                <?php else : ?>
                  <div style="padding: 40px; border: 2px dashed #ccc; border-radius: 8px; color: #888;">
                    Tidak ada gambar
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- New Image Upload -->
          <div class="form-row">
  <div class="form-group full-width">
    <label for="image">Ganti Gambar (Opsional)</label>
    <input type="file" name="image" id="image" accept="image/*" onchange="previewImage(this)">
    <img id="preview" class="preview-image" alt="Preview">
    <p style="font-size: 12px; color: red; margin-top: 5px;">
      * Biarkan kosong jika tidak ingin mengganti gambar<br>
      * Format: JPG, JPEG, PNG, WEBP (Maksimal 2MB)
    </p>
  </div>
</div>


          <!-- Buttons -->
          <div class="button-group">
            <button type="submit" name="update_product" class="btn btn-primary">
              <i class='bx bx-check'></i> Update Produk
            </button>
            <a href="view_product.php" class="btn btn-secondary">
              <i class='bx bx-arrow-back'></i> Kembali
            </a>
          </div>
        </form>
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

function previewImage(input) {
  const preview = document.getElementById('preview');
  
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
    }
    
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.style.display = 'none';
  }
}

// Auto close warning messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(function() {
    const messages = document.querySelectorAll('.message');
    messages.forEach(function(message) {
      message.style.display = 'none';
    });
  }, 5000);
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
  const name = document.getElementById('name').value.trim();
  const price = document.getElementById('price').value;
  const stock = document.getElementById('stock').value;
  
  if (!name || !price || !stock) {
    e.preventDefault();
    alert('Mohon lengkapi semua field yang wajib diisi (*)');
    return false;
  }
  
  if (parseFloat(price) < 0) {
    e.preventDefault();
    alert('Harga tidak boleh negatif');
    return false;
  }
  
  if (parseInt(stock) < 0) {
    e.preventDefault();
    alert('Stok tidak boleh negatif');
    return false;
  }
});
</script>

</body>
</html>