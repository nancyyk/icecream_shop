<?php
include '../components/connect.php';

if (isset($_POST['submit'])) {
    $id = unique_id();
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);
    $cpass = sha1($_POST['cpass']);
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);
    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $ext = pathinfo($image, PATHINFO_EXTENSION);
    $rename = unique_id() . '.' . $ext;
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../uploaded_files/' . $rename;
    $account_type = $_POST['account_type']; // Mendapatkan jenis akun dari form

    if ($pass != $cpass) {
        $warning_msg[] = 'confirm password not matched';
    } else {
        if ($account_type === 'seller') {
            $select_seller = $conn->prepare("SELECT * FROM sellers WHERE email = ?");
            $select_seller->execute([$email]);
            if ($select_seller->rowCount() > 0) {
                $warning_msg[] = 'email already exist for seller!';
            } else {
                $insert_seller = $conn->prepare("INSERT INTO sellers(id, name, email, password, image) VALUES(?, ?, ?, ?, ?)");
                $insert_seller->execute([$id, $name, $email, $pass, $rename]);
                move_uploaded_file($image_tmp_name, $image_folder);
                $success_msg[] = 'new seller registered! please login now';
                $redirect_login = '../admin_panel/login_universal.php';
            }
        } elseif ($account_type === 'user') {
            $select_user = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $select_user->execute([$email]);
            if ($select_user->rowCount() > 0) {
                $warning_msg[] = 'email already exist for user!';
            } else {
                $insert_user = $conn->prepare("INSERT INTO users(id, name, email, password, image) VALUES(?, ?, ?, ?, ?)");
                $insert_user->execute([$id, $name, $email, $pass, $rename]);
                move_uploaded_file($image_tmp_name, $image_folder);
                $success_msg[] = 'new user registered! please login now';
                $redirect_login = '../admin_panel/login_universal.php';
            }
        } else {
            $warning_msg[] = 'invalid account type selected!';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aurora Gelato - Universal Registration Page</title>
    <link rel="stylesheet" type="text/css" href="../css/admin_style.css">
    <link rel="stylesheet" href="register.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }

        .checkmark-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #e9f7ef;
            margin: 0 auto 20px;
        }

        .checkmark {
            color: #4CAF50;
            font-size: 36px;
        }

        .modal-message {
            font-size: 18px;
            color: #555;
            margin-bottom: 20px;
        }

        .ok-btn {
            background-color: #7ac080;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .ok-btn:hover {
            background-color: #68a86c;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="title">Register Account</h2>
    <form method="POST" enctype="multipart/form-data" class="form-wrapper">
        <div class="form-group">
            <label>Account Type <span class="required">*</span></label>
            <select name="account_type" required>
                <option value="">Select Account Type</option>
                <option value="seller">Admin (Seller)</option>
                <option value="user">User</option>
            </select>
        </div>
        <div class="form-group">
            <label>Your Name <span class="required">*</span></label>
            <input type="text" name="name" placeholder="enter your name" required>
        </div>
        <div class="form-group">
            <label>Your Email <span class="required">*</span></label>
            <input type="email" name="email" placeholder="enter your email" required>
        </div>
        <div class="form-group">
            <label>Your Password <span class="required">*</span></label>
            <input type="password" name="pass" placeholder="enter your password" required>
        </div>
        <div class="form-group">
            <label>Confirm Password <span class="required">*</span></label>
            <input type="password" name="cpass" placeholder="confirm your password" required>
        </div>
        <div class="form-group" style="width: 100%;">
            <label>Your Profile <span class="required">*</span></label>
            <input type="file" name="image" accept="image/*" required>
        </div>
        <div class="login-link">
            Already Have An Account? <a href="login_universal.php">Login Now</a>
        </div>
        <div class="button-container">
            <button type="submit" name="submit" class="register-btn">Register Now</button>
        </div>
    </form>
</div>

<div id="successModal" class="modal">
    <div class="modal-content">
        <div class="checkmark-circle">
            <span class="checkmark">✓</span>
        </div>
        <p class="modal-message">new account registered! please login now</p>
        <button class="ok-btn" onclick="redirectToLogin()">OK</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/script.js"></script>

<script>
    // Check if registration was successful
    <?php if(isset($success_msg)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('successModal').style.display = 'flex';
        });
    <?php endif; ?>

    function redirectToLogin() {
        <?php if(isset($redirect_login)): ?>
            window.location.href = '<?php echo $redirect_login; ?>';
        <?php else: ?>
            window.location.href = 'login_universal.php'; // Default redirect
        <?php endif; ?>
    }
</script>

<?php include '../components/alert.php'; ?>
</body>
</html>