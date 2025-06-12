<?php 
include '../components/connect.php';
session_start();
$seller_id = $_SESSION['seller_id'] ?? '';

// Redirect jika belum login
if (!$seller_id) {
  header('location:seller_login.php');
  exit();
}

// Inisialisasi variabel pesan
$success_msg = [];
$warning_msg = [];

if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
  $image = $_FILES['image']['name'];
  $image_size = $_FILES['image']['size'];
  $image_tmp_name = $_FILES['image']['tmp_name'];
  $image_folder = '../uploaded_files/'.$image;

  // CEK GAMBAR SUDAH ADA ATAU BELUM DI DATABASE
  $select_image = $conn->prepare("SELECT image FROM products WHERE image = ?");
  $select_image->execute([$image]);

  if ($select_image->rowCount() > 0) {
    $warning_msg[] = 'image name repeated';
  } elseif ($image_size > 2000000) {
    $warning_msg[] = 'image size is too large';
  } else {
    move_uploaded_file($image_tmp_name, $image_folder);
  }
} else {
  $image = '';
}

// Simpan data jika tombol submit ditekan
if (isset($_POST['publish']) || isset($_POST['draft'])) {
  $name = $_POST['name'];
  $stock = $_POST['stock']; // Diubah dari price menjadi stock
  $price = $_POST['price']; // Menambahkan variabel price
  $status = isset($_POST['publish']) ? 'active' : 'draft';

  // Validasi input
  if (empty($name) || empty($stock) || empty($price)) {
    $warning_msg[] = 'Semua field wajib diisi!';
  } else {
    // Bersihkan input
    $name = htmlspecialchars($name);
    $stock = (int)$stock;
    $price = (int)$price;

    // Insert data ke database
    $insert_product = $conn->prepare("INSERT INTO products (seller_id, name, stock, price, image, status) VALUES (?, ?, ?, ?, ?, ?)");
    $insert_product->execute([$seller_id, $name, $stock, $price, $image, $status]);

    if ($insert_product) {
      $success_msg[] = 'Produk berhasil ditambahkan!';
    } else {
      $warning_msg[] = 'Gagal menambahkan produk.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Gelato Ice - Tambah Produk</title>
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

    .box-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px;
      margin-top: 30px;
    }

    .small-box {
      background-color: #fff;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 1px 6px rgba(0,0,0,0.08);
      width: 180px;
      text-align: center;
    }

    .small-box h3 {
      font-size: 18px;
      color: #e91e63;
      margin-bottom: 8px;
    }

    .small-box p {
      font-size: 14px;
      color: #555;
      margin-bottom: 12px;
    }

    .btn {
      display: inline-block;
      padding: 6px 15px;
      background-color: #f8bbd0;
      color: #c2185b;
      border-radius: 16px;
      text-decoration: none;
      font-size: 13px;
      font-weight: bold;
      transition: background-color 0.3s, color 0.3s;
    }

    .btn:hover {
      background-color: #e91e63;
      color: white;
    }

    @media (max-width: 768px) {
      .box-container {
        gap: 20px;
      }
      .small-box {
        width: 45%;
      }
    }

    @media (max-width: 480px) {
      .main-layout {
        flex-direction: column;
      }
      .sidebar {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #ddd;
      }
      .small-box {
        width: 100%;
      }
    }
    /* FORM BOX - HORIZONTAL LAYOUT */
.form-box {
  background-color: #F3D0D7;
  padding: 40px;
  border-radius: 15px;
  width: 95%;
  max-width: 1200px;
  margin: 30px auto;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 30px;
  box-sizing: border-box;
}

.heading {
  grid-column: 1 / -1;
  text-align: center;
  margin-bottom: 20px;
}

.heading h1 {
  font-size: 32px;
  color: #000;
  text-transform: uppercase;
  font-weight: bold;
}

.input-field {
  margin-bottom: 25px;
}

.input-field p {
  margin: 0 0 15px 0;
  font-size: 20px;
  font-weight: bold;
  color: #333;
}

.box {
  width: 100%;
  padding: 18px;
  border-radius: 10px;
  border: 2px solid #ccc;
  font-size: 18px;
  background-color: #ffffff;
  box-sizing: border-box;
}

textarea.box {
  height: 150px;
  resize: vertical;
}

.flex-btn {
  grid-column: 1 / -1;
  display: flex;
  justify-content: center;
  gap: 30px;
  margin-top: 20px;
}

.btn {
  padding: 18px 50px;
  font-size: 18px;
  border-radius: 25px;
  background-color: #f8bbd0;
  color: #c2185b;
  border: none;
  cursor: pointer;
  font-weight: bold;
  transition: all 0.3s ease;
}

.btn:hover {
  background-color: #e91e63;
  color: white;
}

/* RESPONSIVE ADJUSTMENTS */
@media (max-width: 992px) {
  .form-box {
    grid-template-columns: 1fr;
    gap: 25px;
  }
  
  .btn {
    padding: 15px 40px;
  }
}

@media (max-width: 576px) {
  .form-box {
    padding: 30px;
    width: 100%;
    border-radius: 0;
  }
  
  .input-field p {
    font-size: 18px;
  }
  
  .box {
    padding: 15px;
    font-size: 16px;
  }
  
  .flex-btn {
    flex-direction: column;
    gap: 15px;
  }
  
  .btn {
    width: 100%;
  }
}

.required {
    color: red;
    margin-left: 4px;
  }

.modal {
  display: none; 
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.4);
  justify-content: center;
  align-items: center;
}

.modal-content {
  background-color: #fff;
  padding: 30px;
  border-radius: 20px;
  text-align: center;
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.checkmark-circle {
  width: 100px;
  height: 90px;
  border-radius: 50%;
  background: #FF90BC;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 30px;
}

.checkmark {
  font-size: 36px;
  color: white;
}

.modal-message {
  font-size: 18px;
  margin-bottom: 20px;
}

.ok-btn {
  padding: 10px 25px;
  font-size: 16px;
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 25px;
  cursor: pointer;
}

.ok-btn:hover {
  background-color: #45a049;
}

/* Alert messages */
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
  <div class="main-container">
    <section class="post-editor">
      <div class="heading">
        <h1>Add Product</h1>
      </div>

      <!-- Display success or error messages -->
      <?php if (!empty($success_msg)) { ?>
        <?php foreach($success_msg as $msg) { ?>
          <div class="message success"><?= $msg; ?></div>
        <?php } ?>
      <?php } ?>
      
      <?php if (!empty($warning_msg)) { ?>
        <?php foreach($warning_msg as $msg) { ?>
          <div class="message error"><?= $msg; ?></div>
        <?php } ?>
      <?php } ?>

      <div class="form-container">
        <form action="" method="post" enctype="multipart/form-data" class="form-box" style="margin-top: 20px;">
          <div class="input-field">
            <p>Product Name <span class="required">*</span></p>
            <input type="text" name="name" maxlength="100" placeholder="Vanilla Ice Cream" required class="box">
          </div>

          <div class="input-field">
            <p>Product Stock <span class="required">*</span></p>
            <input type="number" name="stock" min="0" placeholder="9" required class="box">
          </div>

          <div class="input-field">
            <p>Product View <span class="required">*</span></p>
            <input type="file" name="image" accept="image/*" required class="box">
          </div>

          <div class="input-field">
            <p>Product Price <span class="required">*</span></p>
            <input type="number" name="price" min="0" placeholder="25000" required class="box">
          </div>

          <div class="flex-btn">
            <input type="submit" name="publish" value="Add Product" class="btn">
          </div>
        </form>
      </div>
    </section>
  </div>
</div>

<?php if (!empty($success_msg)) : ?>
  <div id="successModal" class="modal" style="display: flex;">
    <div class="modal-content">
      <div class="checkmark-circle">
        <div class="checkmark">&#10004;</div>
      </div>
      <div class="modal-message">Product added successfully!</div>
      <button class="ok-btn" onclick="closeModal()">OK</button>
    </div>
  </div>
<?php endif; ?>

<script>
  function closeModal() {
    document.getElementById("successModal").style.display = "none";
    window.location.href = "view_product.php";
  }
</script>

<script>
  function showSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) modal.style.display = 'block';
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
</script>

</body>
</html>