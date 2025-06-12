<?php 
include '../components/connect.php';
session_start();
$seller_id = $_SESSION['seller_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Gelato Ice - Shop</title>
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
    .sidebar { width: 250px; background-color: #fff; border-right: 1px solid #e0e0e0; padding: 20px; min-height: calc(100vh - 110px); }
    .sidebar img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 10px auto; border: 2px solid #ff69b4; display: block; }
    .sidebar .name { text-align: center; font-weight: bold; margin-bottom: 20px; color: #444; }
    .menu-title { color: #e91e63; font-weight: bold; margin-bottom: 10px; }
    .menu a { display: block; padding: 10px 15px; margin-bottom: 10px; background-color: #f9f9f4; border: 1px solid #ccc; border-radius: 10px; text-decoration: none; color: #444; font-weight: bold; transition: 0.3s; font-size: 14px; }
    .menu a:hover { background-color: #f3f3f3; border-color: #e91e63; color: #e91e63; }

    .social-links { text-align: center; margin-top: 20px; }
    .social-links i { margin: 0 5px; font-size: 18px; color: #555; cursor: pointer; }

    .content { flex: 1; padding: 40px; }
    .heading { text-align: center; margin-bottom: 30px; }
    .heading h1 { font-size: 26px; color: #444; text-transform: uppercase; position: relative; display: inline-block; }
    .heading h1:after { content: ''; position: absolute; width: 60%; height: 3px; background-color: #ff69b4; bottom: -10px; left: 50%; transform: translateX(-50%); }

    .box-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 25px;
      margin-top: 40px;
    }

    .dashboard-box {
      background-color: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.08);
      text-align: center;
      transition: transform 0.3s, box-shadow 0.3s;
      border-top: 4px solid #ff69b4;
    }
    
    .dashboard-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .dashboard-box h3 {
      font-size: 28px;
      color: #e91e63;
      margin-bottom: 8px;
      font-weight: bold;
    }

    .dashboard-box p {
      font-size: 15px;
      color: #666;
      margin-bottom: 15px;
    }

    .btn {
      display: inline-block;
      padding: 8px 18px;
      background-color: #f8bbd0;
      color: #c2185b;
      border-radius: 20px;
      text-decoration: none;
      font-size: 14px;
      font-weight: bold;
      transition: background-color 0.3s, color 0.3s;
      border: none;
      cursor: pointer;
    }

    .btn:hover {
      background-color: #e91e63;
      color: white;
    }

    @media (max-width: 1024px) {
      .box-container {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      }
    }

    @media (max-width: 768px) {
      .box-container {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      }
      .dashboard-box {
        padding: 15px;
      }
    }

    @media (max-width: 576px) {
      .main-layout {
        flex-direction: column;
      }
      .sidebar {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #ddd;
        min-height: auto;
        padding-bottom: 20px;
      }
      .box-container {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      }
      .top-bar {
        padding: 15px 20px;
      }
      .logo-container img {
        height: 50px;
      }
      .logo-text {
        font-size: 28px;
      }
      .content {
        padding: 20px;
      }
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
        <a href="../admin_panel/home.php" onclick="return confirm('Logout from this website?');" class="button logout-btn">Logout</a>
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
    <div class="heading">
      <h1>Dashboard</h1>
    </div>
    <div class="box-container">
      <!-- Welcome box -->
      <div class="dashboard-box">
        <h3>Welcome!</h3>
        <p><?= $seller_name ?></p>
        <a href="../components/update.php" class="btn">Update Profile</a>
      </div>

      <!-- All Products box -->
      <?php 
        $select_products = $conn->prepare("SELECT * FROM products WHERE seller_id = ?");
        $select_products->execute([$seller_id]);
        $number_of_products = $select_products->rowCount();
      ?>
      <div class="dashboard-box">
        <h3><?= $number_of_products; ?></h3>
        <p>All Products</p>
        <a href="add_products.php" class="btn">Add New</a>
      </div>

      <!-- Active Products box -->
      <?php 
        $select_active_products = $conn->prepare("SELECT * FROM products WHERE seller_id = ? AND status = ?");
        $select_active_products->execute([$seller_id, 'active']);
        $number_of_active_products = $select_active_products->rowCount();
      ?>
      <div class="dashboard-box">
        <h3><?= $number_of_active_products; ?></h3>
        <p>Active Products</p>
        <a href="view_product.php" class="btn">View All</a>
      </div>

      <!-- Users box -->
      <?php 
        $select_users = $conn->prepare("SELECT * FROM users");
        $select_users->execute();
        $number_of_users = $select_users->rowCount();
      ?>
      <div class="dashboard-box">
        <h3><?= $number_of_users; ?></h3>
        <p>Total Users</p>
        <a href="../components/users_accounts.php" class="btn">View Accounts</a>
      </div>
      </div>
    </div>
  </div>
</div>

<script>
  function toggleUserPopup() {
    const popup = document.getElementById('userPopup');
    popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
  }
  
  // Close popup when clicking outside
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