<?php
ob_start();
session_start();
include '../components/connect.php';

$login_status = '';
$login_message = '';
$redirect_page = '';

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $pass = $_POST['pass'];

    $hashed_password = sha1($pass);

    // Cek tabel sellers
    $seller_query = $conn->prepare("SELECT * FROM sellers WHERE email = ?");
    $seller_query->execute([$email]);

    if ($seller_query->rowCount() > 0) {
        $seller = $seller_query->fetch(PDO::FETCH_ASSOC);
        if ($hashed_password === $seller['password']) {
            $_SESSION['seller_id'] = $seller['id'];
            $login_status = 'success';
            $login_message = 'Login berhasil sebagai Seller!';
            $redirect_page = 'dashboard.php';
        } else {
            $login_status = 'error';
            $login_message = 'Password salah untuk seller!';
        }
    } else {
        // Cek tabel users
        $user_query = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $user_query->execute([$email]);

        if ($user_query->rowCount() > 0) {
            $user = $user_query->fetch(PDO::FETCH_ASSOC);
            if ($hashed_password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $login_status = 'success';
                $login_message = 'Login berhasil sebagai User!';
                $redirect_page = '../admin_panel/view1_product.php';
            } else {
                $login_status = 'error';
                $login_message = 'Password salah untuk user!';
            }
        } else {
            $login_status = 'error';
            $login_message = 'Email tidak ditemukan!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Page - Universal</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f9f9f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 700px;
            padding: 40px;
        }

        .login-heading {
            text-align: center;
            color: #d94a70;
            font-size: 28px;
            margin-bottom: 30px;
            font-weight: normal;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #555;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group label::after {
            content: " *";
            color: #d94a70;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }

        .validation-message {
            color: #d94a70;
            font-size: 12px;
            margin-top: 5px;
            text-align: right;
            display: none;
        }

        .validation-message.show {
            display: block;
        }

        .login-button {
            display: block;
            width: 100%;
            background-color: #f8f8f8;
            color: #d94a70;
            border: none;
            padding: 14px;
            border-radius: 50px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .login-button:hover {
            background-color: #f1f1f1;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #555;
        }

        .register-now {
            color: #d94a70;
            text-decoration: none;
            font-weight: 500;
        }

        .register-now:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1 class="login-heading">Login Now</h1>
        <form action="" method="post">
            <div class="form-group">
                <label for="email">Your Email</label>
                <input type="email" id="email" name="email" placeholder="enter your email" required>
                <div class="validation-message" id="email-validation">Please fill out this field</div>
            </div>
            <div class="form-group">
                <label for="password">Your Password</label>
                <input type="password" id="password" name="pass" placeholder="enter your password" required>
            </div>
           <div class="register-link">
                Don't have an account? <a href="register_universal.php" class="register-now">Register now</a>
            </div>
            <button type="submit" name="submit" class="login-button">Login Now</button>
        </form>
    </div>

    <script>
        document.getElementById('email').addEventListener('blur', function() {
            document.getElementById('email-validation').classList.toggle('show', this.value === '');
        });

        <?php if ($login_status === 'success') : ?>
            Swal.fire({
                icon: 'success',
                title: '<?= $login_message ?>',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = '<?= $redirect_page ?>';
            });
        <?php elseif ($login_status === 'error') : ?>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: '<?= $login_message ?>',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>
    </script>
</body>
</html>