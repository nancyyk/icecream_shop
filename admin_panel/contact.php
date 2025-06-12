<?php 
session_start();
include '../components/connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - Gelato Ice Shop</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  <style>
    :root {
      --pink: #ff69b4;
      --dark-pink: #e91e63;
      --light-pink: #f8bbd0;
      --cream: #f9f9f4;
      --dark-gray: #333;
      --light-gray: #f3f3f3;
      --success: #4CAF50;
      --error: #F44336;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }
    
    body {
      background-color: var(--cream);
      color: var(--dark-gray);
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    /* Header Styles */
    header {
      background-color: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    
    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
    }
    
    .logo {
      display: flex;
      align-items: center;
    }
    
    .logo img {
      height: 60px;
      margin-right: 15px;
    }
    
    .logo-text {
      font-size: 32px;
      font-weight: bold;
      color: var(--pink);
    }
    
    .highlight {
      font-style: italic;
      font-weight: normal;
      font-size: 24px;
      color: var(--dark-pink);
    }
    
    .menu {
      display: flex;
      list-style: none;
    }
    
    .menu li {
      margin-left: 25px;
    }
    
    .menu a {
      text-decoration: none;
      color: var(--dark-gray);
      font-weight: 500;
      font-size: 16px;
      transition: color 0.3s;
    }
    
    .menu a:hover, .menu a.active {
      color: var(--pink);
    }
    
    .mobile-menu-btn {
      display: none;
      font-size: 24px;
      cursor: pointer;
    }
    
    /* Page Banner */
    .page-banner {
      background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('image/ice-cream-bg.jpg');
      background-size: cover;
      background-position: center;
      color: white;
      text-align: center;
      padding: 80px 20px;
      margin-bottom: 50px;
    }
    
    .page-banner h1 {
      font-size: 48px;
      margin-bottom: 15px;
      text-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
    
    .page-banner p {
      font-size: 18px;
      max-width: 700px;
      margin: 0 auto;
      line-height: 1.6;
    }
    
    /* Contact Section */
    .contact-section {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      margin-bottom: 50px;
    }
    
    .contact-info {
      flex: 1;
      min-width: 300px;
    }
    
    .contact-form {
      flex: 1;
      min-width: 300px;
    }
    
    .section-title {
      color: var(--dark-pink);
      font-size: 28px;
      margin-bottom: 25px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--light-pink);
    }
    
    .info-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 25px;
    }
    
    .info-icon {
      width: 50px;
      height: 50px;
      background-color: var(--light-pink);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      color: var(--dark-pink);
      font-size: 20px;
    }
    
    .info-content h4 {
      font-size: 18px;
      margin-bottom: 5px;
    }
    
    .info-content p, .info-content a {
      color: #666;
      line-height: 1.5;
      text-decoration: none;
    }
    
    .info-content a:hover {
      color: var(--pink);
    }
    
    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 30px;
    }
    
    .social-links a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background-color: var(--light-pink);
      color: var(--dark-pink);
      border-radius: 50%;
      font-size: 18px;
      transition: all 0.3s;
    }
    
    .social-links a:hover {
      background-color: var(--pink);
      color: white;
      transform: translateY(-3px);
    }
    
    /* Form Styles */
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
    }
    
    .form-group input, 
    .form-group textarea, 
    .form-group select {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 16px;
      transition: border-color 0.3s;
    }
    
    .form-group input:focus, 
    .form-group textarea:focus, 
    .form-group select:focus {
      border-color: var(--pink);
      outline: none;
    }
    
    .form-group textarea {
      height: 150px;
      resize: vertical;
    }
    
    .submit-btn {
      background-color: var(--pink);
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 30px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .submit-btn:hover {
      background-color: var(--dark-pink);
      transform: translateY(-2px);
    }
    
    /* Store Hours */
    .store-hours {
      background-color: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      margin-bottom: 50px;
    }
    
    .hours-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .hours-table th, .hours-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    
    .hours-table th {
      color: var(--dark-pink);
      font-weight: 600;
    }
    
    .hours-table tr:last-child td {
      border-bottom: none;
    }
    
    .current-day {
      font-weight: bold;
      color: var(--dark-pink);
    }
    
    /* Map Section */
    .map-section {
      margin-bottom: 50px;
    }
    
    .map-container {
      height: 400px;
      border-radius: 10px;
      overflow: hidden;
    }
    
    .map-container iframe {
      width: 100%;
      height: 100%;
      border: none;
    }
    
    /* FAQ Section */
    .faq-section {
      margin-bottom: 50px;
    }
    
    .accordion {
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 10px;
    }
    
    .accordion-header {
      padding: 15px 20px;
      background-color: #f8f8f8;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: 600;
    }
    
    .accordion-header:hover {
      background-color: #f1f1f1;
    }
    
    .accordion-content {
      padding: 0;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease-out;
    }
    
    .accordion-content-inner {
      padding: 15px 20px;
      border-top: 1px solid #ddd;
      line-height: 1.6;
    }
    
    .accordion.active .accordion-content {
      max-height: 200px;
    }
    
    /* Alert Messages */
    .message {
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 25px;
      font-weight: 500;
    }
    
    .success-message {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .error-message {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    /* Footer */
    footer {
      background-color: #222;
      color: #fff;
      padding: 50px 0 20px;
    }
    
    .footer-content {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 30px;
      margin-bottom: 30px;
    }
    
    .footer-column {
      flex: 1;
      min-width: 200px;
    }
    
    .footer-title {
      color: var(--pink);
      font-size: 20px;
      margin-bottom: 20px;
      position: relative;
      padding-bottom: 10px;
    }
    
    .footer-title::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      width: 50px;
      height: 2px;
      background-color: var(--pink);
    }
    
    .footer-links {
      list-style: none;
    }
    
    .footer-links li {
      margin-bottom: 10px;
    }
    
    .footer-links a {
      color: #ccc;
      text-decoration: none;
      transition: color 0.3s;
    }
    
    .footer-links a:hover {
      color: var(--pink);
    }
    
    .footer-bottom {
      text-align: center;
      padding-top: 20px;
      border-top: 1px solid #444;
      color: #888;
      font-size: 14px;
    }
    
    /* Responsive Styles */
    @media (max-width: 768px) {
      .navbar {
        padding: 15px;
      }
      
      .menu {
        display: none;
        position: absolute;
        top: 90px;
        left: 0;
        right: 0;
        background-color: white;
        flex-direction: column;
        padding: 20px;
        box-shadow: 0 5px 10px rgba(0,0,0,0.1);
      }
      
      .menu.active {
        display: flex;
      }
      
      .menu li {
        margin: 0 0 15px 0;
      }
      
      .mobile-menu-btn {
        display: block;
      }
      
      .logo img {
        height: 50px;
      }
      
      .logo-text {
        font-size: 28px;
      }
      
      .highlight {
        font-size: 20px;
      }
      
      .page-banner h1 {
        font-size: 36px;
      }
      
      .page-banner p {
        font-size: 16px;
      }
      
      .section-title {
        font-size: 24px;
      }
    }
    
    @media (max-width: 480px) {
      .info-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
      }
      
      .logo-text {
        font-size: 24px;
      }
      
      .highlight {
        font-size: 18px;
      }
      
      .page-banner h1 {
        font-size: 32px;
      }
      
      .submit-btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <!-- Header Section -->
  <header>
    <div class="container">
      <nav class="navbar">
        <div class="logo">
          <img src="../image/logo.jpeg" alt="Gelato Ice Logo">
          <div class="logo-text">G̾e̾l̾a̾t̾o̾ <span class="highlight">𝑖𝑐𝑒</span></div>
        </div>
        <ul class="menu" id="menu">
          <li><a href="../admin_panel/home.php">Home</a></li>
          <li><a href="../admin_panel/view1_product.php">Products</a></li>
          <li><a href="../admin_panel/contact.php" class="active">Contact</a></li>
          <?php if(isset($_SESSION['user_id'])): ?>
          <?php endif; ?>
        </ul>
        <div class="mobile-menu-btn" id="mobileMenuBtn">
          <i class="fas fa-bars"></i>
        </div>
      </nav>
    </div>
  </header>

  <!-- Page Banner -->
  <section class="page-banner">
    <div class="container">
      <h1>Contact Us</h1>
      <p>Have questions, suggestions, or just want to chat about delicious gelato? Our friendly team is ready to help! Come on, contact us!</p>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="container">
    <div class="contact-section">
      <div class="contact-info">
        <h2 class="section-title">Our Contact Information</h2>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-map-marker-alt"></i>
          </div>
          <div class="info-content">
            <h4>Our Location</h4>
            <p>Jl. Veteran 01<br>Lamongan, 62211<br>Indonesia</p>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-phone-alt"></i>
          </div>
          <div class="info-content">
            <h4>Phone Number</h4>
            <p><a href="tel:+62215551234">(021) 555-1234</a></p>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-envelope"></i>
          </div>
          <div class="info-content">
            <h4>Email Address</h4>
            <p><a href="mailto:hello@gelatoice.com">hello@gelatoice.com</a></p>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-clock"></i>
          </div>
          <div class="info-content">
            <h4>Opening Hours</h4>
            <p>Monday - Friday: 10:00 AM - 9:00 PM<br>
              Saturday - Sunday: 11:00 AM - 10:00 PM</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Store Hours Section -->
  <section class="container">
    <div class="store-hours">
      <h2 class="section-title">Our Store Hours</h2>
      <table class="hours-table">
        <tr>
          <th>Day</th>
          <th>Opening Hours</th>
          <th>Special Notes</th>
        </tr>
        <?php
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $hours = [
          'Monday' => '10:00 AM - 9:00 PM',
          'Tuesday' => '10:00 AM - 9:00 PM',
          'Wednesday' => '10:00 AM - 9:00 PM',
          'Thursday' => '10:00 AM - 9:00 PM',
          'Friday' => '10:00 AM - 9:00 PM',
          'Saturday' => '11:00 AM - 10:00 PM',
          'Sunday' => '11:00 AM - 10:00 PM'
        ];
        
        $notes = [
          'Monday' => '',
          'Tuesday' => '',
          'Wednesday' => 'Happy Hour: 2PM - 5PM',
          'Thursday' => '',
          'Friday' => 'New Flavor Friday!',
          'Saturday' => 'Kids Special Day',
          'Sunday' => 'Family Discount Day'
        ];
        
        $current_day = date('l');
        
        foreach($days as $day) {
          $class = ($day == $current_day) ? 'current-day' : '';
          echo "<tr>";
          echo "<td class='$class'>$day</td>";
          echo "<td class='$class'>{$hours[$day]}</td>";
          echo "<td class='$class'>{$notes[$day]}</td>";
          echo "</tr>";
        }
        ?>
      </table>
    </div>
  </section>

  <!-- Map Section -->
  <section class="container map-section">
    <h2 class="section-title">Find Us</h2>
    <div class="map-container">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126933.05169938862!2d106.7228756069678!3d-6.245132647189746!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f1ec2422b0b3%3A0x39a0d0fe47404d02!2sSouth%20Jakarta%2C%20Jakarta%2C%20Indonesia!5e0!3m2!1sen!2sid!4v1715873025412!5m2!1sen!2sid" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </section>

  <!-- FAQ Section -->
  <section class="container faq-section">
    <h2 class="section-title">Frequently Asked Questions</h2>
    
    <div class="accordion">
      <div class="accordion-header">
        <span>Do you offer catering services?</span>
        <i class="fas fa-plus"></i>
      </div>
      <div class="accordion-content">
        <div class="accordion-content-inner">
          Yes, we offer catering services for events of all sizes! Whether it's a birthday party, wedding, corporate event, or any special occasion, we can create a delicious gelato experience for your guests. Please contact us at least 7 days in advance to discuss your requirements.
        </div>
      </div>
    </div>
    
    <div class="accordion">
      <div class="accordion-header">
        <span>Are your gelatos suitable for vegans?</span>
        <i class="fas fa-plus"></i>
      </div>
      <div class="accordion-content">
        <div class="accordion-content-inner">
          We have a selection of vegan-friendly gelato options made with plant-based milk alternatives like coconut, almond, and soy. Our fruit sorbets are also 100% vegan. Just ask our staff for the vegan options available on the day of your visit!
        </div>
      </div>
    </div>
    
    <div class="accordion">
      <div class="accordion-header">
        <span>Do you deliver gelato to homes?</span>
        <i class="fas fa-plus"></i>
      </div>
      <div class="accordion-content">
        <div class="accordion-content-inner">
          Yes! We offer delivery within Jakarta through our website and partner food delivery services. Minimum order may apply depending on your location. We use special insulated packaging to ensure your gelato arrives in perfect condition.
        </div>
      </div>
    </div>
    
    <div class="accordion">
      <div class="accordion-header">
        <span>Do you have gluten-free options?</span>
        <i class="fas fa-plus"></i>
      </div>
      <div class="accordion-content">
        <div class="accordion-content-inner">
          Most of our gelato flavors are gluten-free by nature. However, flavors containing cookies, cake pieces, or certain mix-ins may contain gluten. Our staff can guide you to completely gluten-free options when you visit. We take precautions to minimize cross-contamination but our kitchen is not certified gluten-free.
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="footer-content">
        <div class="footer-column">
          <h3 class="footer-title">About Us</h3>
          <p style="color: #ccc; line-height: 1.6; margin-bottom: 15px;">
            Have questions, suggestions, or just want to chat about delicious gelato? Our friendly team is ready to help! Come on, contact us!</p>
        </div>
        
        <div class="footer-column">
          <h3 class="footer-title">Contact Info</h3>
          <ul class="footer-links">
            <li style="color: #ccc; margin-bottom: 10px;">
              <i class="fas fa-map-marker-alt" style="margin-right: 10px;"></i> Jl. Veteran 01 Lamongan, 62211 Indonesia
            </li>
            <li style="color: #ccc; margin-bottom: 10px;">
              <i class="fas fa-phone" style="margin-right: 10px;"></i> (021) 555-1234
            </li>
            <li style="color: #ccc; margin-bottom: 10px;">
              <i class="fas fa-envelope" style="margin-right: 10px;"></i> hello@gelatoice.com
            </li>
          </ul>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // Mobile Menu Toggle
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
      document.getElementById('menu').classList.toggle('active');
    });
    
    // FAQ Accordion
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    
    accordionHeaders.forEach(header => {
      header.addEventListener('click', function() {
        const accordion = this.parentElement;
        const accordionContent = this.nextElementSibling;
        const icon = this.querySelector('i');
        
        // Toggle current accordion
        accordion.classList.toggle('active');
        
        // Change icon
        if (accordion.classList.contains('active')) {
          icon.classList.remove('fa-plus');
          icon.classList.add('fa-minus');
        } else {
          icon.classList.remove('fa-minus');
          icon.classList.add('fa-plus');
        }
        
        // Close other accordions
        accordionHeaders.forEach(otherHeader => {
          if (otherHeader !== header) {
            otherHeader.parentElement.classList.remove('active');
            const otherIcon = otherHeader.querySelector('i');
            otherIcon.classList.remove('fa-minus');
            otherIcon.classList.add('fa-plus');
          }
        });
      });
    });
    
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