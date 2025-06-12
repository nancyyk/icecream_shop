<?php 
include '../components/connect.php';
session_start();
$seller_id = $_SESSION['seller_id'] ?? '';

if (!$seller_id) {
    header('location: ../admin_panel/login.php');
    exit();
}

// Fetch current user data
$select_seller = $conn->prepare("SELECT * FROM sellers WHERE id = ?");
$select_seller->execute([$seller_id]);
$seller_data = $select_seller->fetch(PDO::FETCH_ASSOC);

if (!$seller_data) {
    header('location: ../admin_panel/login.php');
    exit();
}

$message = '';

// Handle form submission
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($name) || empty($email)) {
        $message = 'Name and email are required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format!';
    } else {
        // Check if email already exists (excluding current user)
        $check_email = $conn->prepare("SELECT id FROM sellers WHERE email = ? AND id != ?");
        $check_email->execute([$email, $seller_id]);
        
        if ($check_email->rowCount() > 0) {
            $message = 'Email already exists!';
        } else {
            // Update basic info
            $update_profile = $conn->prepare("UPDATE sellers SET name = ?, email = ? WHERE id = ?");
            $update_profile->execute([$name, $email, $seller_id]);
            
            // Handle password update
            if (!empty($old_password) && !empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $message = 'New passwords do not match!';
                } elseif (strlen($new_password) < 6) {
                    $message = 'Password must be at least 6 characters!';
                } else {
                    // Verify old password
                    if (password_verify($old_password, $seller_data['password'])) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_password = $conn->prepare("UPDATE sellers SET password = ? WHERE id = ?");
                        $update_password->execute([$hashed_password, $seller_id]);
                        $message = 'Profile and password updated successfully!';
                    } else {
                        $message = 'Current password is incorrect!';
                    }
                }
            } else {
                $message = 'Profile updated successfully!';
            }
            
            // Refresh data
            $select_seller->execute([$seller_id]);
            $seller_data = $select_seller->fetch(PDO::FETCH_ASSOC);
        }
    }
}

// Handle image upload
if (isset($_POST['update_image'])) {
    $image = $_FILES['image'];
    
    if ($image['size'] > 0) {
        $image_name = $image['name'];
        $image_tmp = $image['tmp_name'];
        $image_size = $image['size'];
        
        // Validate image
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            $message = 'Invalid image format! Only JPG, JPEG, PNG, and GIF are allowed.';
        } elseif ($image_size > 5000000) { // 5MB
            $message = 'Image size too large! Maximum 5MB allowed.';
        } else {
            // Generate unique filename
            $new_image_name = uniqid() . '.' . $file_extension;
            $image_path = '../uploaded_files/' . $new_image_name;
            
            if (move_uploaded_file($image_tmp, $image_path)) {
                // Delete old image if exists
                if (!empty($seller_data['image']) && $seller_data['image'] !== 'default_avatar.png') {
                    $old_image_path = '../uploaded_files/' . $seller_data['image'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                
                // Update database
                $update_image = $conn->prepare("UPDATE sellers SET image = ? WHERE id = ?");
                $update_image->execute([$new_image_name, $seller_id]);
                
                $message = 'Profile image updated successfully!';
                
                // Refresh data
                $select_seller->execute([$seller_id]);
                $seller_data = $select_seller->fetch(PDO::FETCH_ASSOC);
            } else {
                $message = 'Failed to upload image!';
            }
        }
    }
}

$profile_img = !empty($seller_data['image']) ? $seller_data['image'] : 'default_avatar.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - Gelato Ice</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body { 
            margin: 0; 
            font-family: 'Segoe UI', sans-serif; 
            background-color: #f9f9f4; 
        }
        
        .top-bar { 
            background-color: #fff; 
            padding: 20px 40px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
        }
        
        .logo-container { 
            display: flex; 
            align-items: center; 
        }
        
        .logo-container img { 
            height: 70px; 
            margin-right: 20px; 
        }
        
        .logo-text { 
            font-size: 36px; 
            font-weight: bold; 
            color: #ff69b4; 
        }
        
        .highlight { 
            font-style: italic; 
            font-weight: normal; 
            font-size: 26px; 
            color: #e91e63; 
        }
        
        .back-btn {
            background-color: #f8bbd0;
            color: #c2185b;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-btn:hover {
            background-color: #e91e63;
            color: white;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .heading {
            text-align: center;
            margin-bottom: 40px;
        }

        .heading h1 {
            font-size: 28px;
            color: #444;
            text-transform: uppercase;
            position: relative;
            display: inline-block;
        }

        .heading h1:after {
            content: '';
            position: absolute;
            width: 60%;
            height: 3px;
            background-color: #ff69b4;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .profile-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .profile-image-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .current-image {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ff69b4;
            margin-bottom: 20px;
        }

        .image-upload-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: inline-block;
            padding: 12px 25px;
            background-color: #f8bbd0;
            color: #c2185b;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .file-input-label:hover {
            background-color: #e91e63;
            color: white;
        }

        .profile-form-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #444;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #ff69b4;
            box-shadow: 0 0 10px rgba(255, 105, 180, 0.2);
        }

        .password-section {
            border-top: 2px solid #f0f0f0;
            padding-top: 25px;
            margin-top: 25px;
        }

        .password-section h3 {
            color: #e91e63;
            margin-bottom: 20px;
        }

        .btn {
            background-color: #f8bbd0;
            color: #c2185b;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn:hover {
            background-color: #e91e63;
            color: white;
            transform: translateY(-2px);
        }

        .btn-image {
            background-color: #4CAF50;
            color: white;
        }

        .btn-image:hover {
            background-color: #45a049;
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .info-card {
            background: linear-gradient(135deg, #ff69b4, #e91e63);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .info-card h2 {
            margin: 0 0 10px 0;
        }

        .info-card p {
            margin: 0;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .container {
                padding: 0 15px;
            }

            .top-bar {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }

            .logo-container {
                text-align: center;
            }

            .current-image {
                width: 150px;
                height: 150px;
            }
        }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
    <div class="logo-container">
        <img src="../image/logo.jpeg" alt="Logo">
        <div class="logo-text">G̾e̾l̾a̾t̾o̾ <span class="highlight">𝑖𝑐𝑒</span></div>
    </div>
    <a href="../admin_panel/admin_header.php" class="back-btn">
        <i class='bx bx-arrow-back'></i>
        Back to Dashboard
    </a>
</div>

<div class="container">
    <div class="heading">
        <h1>Update Profile</h1>
    </div>

    <!-- Display Messages -->
    <?php if (!empty($message)): ?>
        <div class="message <?php echo (strpos($message, 'success') !== false) ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Profile Info Card -->
    <div class="info-card">
        <h2>Welcome, <?php echo htmlspecialchars($seller_data['name']); ?>!</h2>
        <p>Update your profile information and keep your account secure</p>
    </div>

    <div class="profile-container">
        <!-- Profile Image Section -->
        <div class="profile-image-section">
            <img src="../uploaded_files/<?php echo $profile_img; ?>" alt="Profile Image" class="current-image">
            <h3>Profile Picture</h3>
            
            <form method="POST" enctype="multipart/form-data" class="image-upload-form">
                <div class="file-input-wrapper">
                    <input type="file" name="image" accept="image/*" id="imageInput">
                    <label for="imageInput" class="file-input-label">
                        <i class='bx bx-upload'></i> Choose New Image
                    </label>
                </div>
                <button type="submit" name="update_image" class="btn btn-image">
                    <i class='bx bx-check'></i> Update Image
                </button>
            </form>
            
            <p style="font-size: 12px; color: #666; margin-top: 10px;">
                Supported formats: JPG, JPEG, PNG, GIF<br>
                Maximum size: 5MB
            </p>
        </div>

        <!-- Profile Form Section -->
        <div class="profile-form-section">
            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($seller_data['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($seller_data['email']); ?>" required>
                </div>

                <div class="password-section">
                    <h3><i class='bx bx-lock'></i> Change Password (Optional)</h3>
                    
                    <div class="form-group">
                        <label for="old_password">Current Password</label>
                        <input type="password" name="old_password" id="old_password" placeholder="Enter current password">
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" name="new_password" id="new_password" placeholder="Enter new password (min 6 characters)">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password">
                    </div>
                </div>

                <button type="submit" name="update_profile" class="btn">
                    <i class='bx bx-save'></i> Update Profile
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Preview image before upload
    document.getElementById('imageInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('.current-image').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const oldPassword = document.getElementById('old_password').value;

        if (newPassword || confirmPassword || oldPassword) {
            if (!oldPassword) {
                alert('Please enter your current password to change password');
                e.preventDefault();
                return;
            }
            
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match');
                e.preventDefault();
                return;
            }
            
            if (newPassword.length < 6) {
                alert('New password must be at least 6 characters long');
                e.preventDefault();
                return;
            }
        }
    });

    // Auto-hide messages after 5 seconds
    const message = document.querySelector('.message');
    if (message) {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.style.display = 'none';
            }, 300);
        }, 5000);
    }
</script>

</body>
</html>