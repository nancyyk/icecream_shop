<?php
include 'connect.php'; // pastikan path sesuai
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Users Account - Checkout Data</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="style_users_account.css"> <!-- css eksternal -->
</head>
<style>
    body {
  font-family: 'Segoe UI', sans-serif;
  background-color: #fff8f9;
  margin: 0;
  padding: 20px;
}

.container {
  max-width: 1200px;
  margin: auto;
  background-color: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

h1 {
  text-align: center;
  color: #e91e63;
  margin-bottom: 30px;
}

.stats {
  display: flex;
  justify-content: space-around;
  margin-bottom: 30px;
  gap: 20px;
}

.stat-card {
  background: linear-gradient(135deg, #e91e63, #f06292);
  color: white;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
  flex: 1;
}

.stat-card h3 {
  margin: 0;
  font-size: 2em;
}

.stat-card p {
  margin: 5px 0 0 0;
  opacity: 0.9;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

thead {
  background-color: #fce4ec;
  color: #c2185b;
}

th, td {
  padding: 12px 15px;
  border: 1px solid #ddd;
  text-align: left;
}

tbody tr:nth-child(even) {
  background-color: #f9f9f9;
}

tbody tr:hover {
  background-color: #ffeef4;
}

.status {
  padding: 5px 10px;
  border-radius: 15px;
  font-size: 0.8em;
  font-weight: bold;
}

.status.pending {
  background-color: #fff3cd;
  color: #856404;
}

.status.in_progress {
  background-color: #d1ecf1;
  color: #0c5460;
}

.status.completed {
  background-color: #d4edda;
  color: #155724;
}

.method {
  padding: 5px 10px;
  border-radius: 15px;
  font-size: 0.8em;
  font-weight: bold;
  background-color: #e3f2fd;
  color: #1565c0;
}

.method.cash {
  background-color: #e8f5e8;
  color: #2e7d32;
}

.method.transfer {
  background-color: #fff3e0;
  color: #ef6c00;
}

.method.credit {
  background-color: #f3e5f5;
  color: #7b1fa2;
}

.no-data {
  text-align: center;
  color: #666;
  font-style: italic;
}

@media (max-width: 768px) {
  .stats {
    flex-direction: column;
  }
  
  table {
    font-size: 0.9em;
  }
  
  th, td {
    padding: 8px 10px;
  }
}
</style>
<body>

<div class="container">
  <h1>Data Checkout Pengguna</h1>
  
  <?php
    try {
      // Statistik orders
      $stmt_stats = $conn->prepare("SELECT 
        COUNT(*) as total_orders,
        SUM(price * qty) as total_revenue,
        COUNT(DISTINCT user_id) as total_customers
        FROM orders");
      $stmt_stats->execute();
      $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
  ?>
  
  <div class="stats">
    <div class="stat-card">
      <h3><?php echo $stats['total_orders'] ?? 0; ?></h3>
      <p>Total Orders</p>
    </div>
    <div class="stat-card">
      <h3>Rp <?php echo number_format($stats['total_revenue'] ?? 0, 0, ',', '.'); ?></h3>
      <p>Total Revenue</p>
    </div>
    <div class="stat-card">
      <h3><?php echo $stats['total_customers'] ?? 0; ?></h3>
      <p>Total Customers</p>
    </div>
  </div>
  
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Order ID</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Nomor HP</th>
        <th>Alamat</th>
        <th>Produk</th>
        <th>Harga</th>
        <th>Qty</th>
        <th>Total</th>
        <th>Method</th>
        <th>Tanggal</th>
      </tr>
    </thead>
    <tbody>
      <?php
        // Query untuk mengambil data orders berdasarkan struktur tabel yang Anda tunjukkan
        $stmt = $conn->prepare("SELECT 
          order_id, 
          id, 
          name, 
          number, 
          email, 
          address, 
          method, 
          product_id, 
          price, 
          qty, 
          dates,  
          payment_status,
          user_id
          FROM orders 
          ORDER BY dates DESC");
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
          $no = 1;
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $total = $row['price'] * $row['qty'];
            
            // Method styling
            $method_class = '';
            switch(strtolower($row['method'])) {
              case 'cash': $method_class = 'cash'; break;
              case 'transfer': case 'bank transfer': $method_class = 'transfer'; break;
              case 'credit card': case 'credit': $method_class = 'credit'; break;
              default: $method_class = '';
            }
            
            echo "<tr>
              <td>{$no}</td>
              <td>" . htmlspecialchars($row['order_id']) . "</td>
              <td>" . htmlspecialchars($row['name']) . "</td>
              <td>" . htmlspecialchars($row['email']) . "</td>
              <td>" . htmlspecialchars($row['number']) . "</td>
              <td>" . htmlspecialchars($row['address']) . "</td>
              <td>" . htmlspecialchars($row['product_id']) . "</td>
              <td>Rp " . number_format($row['price'], 0, ',', '.') . "</td>
              <td>" . $row['qty'] . "</td>
              <td>Rp " . number_format($total, 0, ',', '.') . "</td>
              <td><span class='method {$method_class}'>" . htmlspecialchars($row['method']) . "</span></td>
              <td>" . date('d-m-Y H:i', strtotime($row['dates'])) . "</td>
            </tr>";
            $no++;
          }
        } else {
          echo "<tr><td colspan='13' class='no-data'>Belum ada data checkout.</td></tr>";
        }
      ?>
    </tbody>
  </table>
  
  <?php
    } catch(PDOException $e) {
      echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
    }
  ?>
</div>

</body>
</html>