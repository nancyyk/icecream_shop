<?php
ob_start(); // Tangani output awal
session_start();
include '../components/connect.php';

$login_status = '';
$login_message = '';

if (isset($_COOKIE['seller_id'])) {
    $seller_id = $_COOKIE['seller_id'];
}else{
    $seller_id = '';
    header('location:admin_header.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ice Cream Shop Admin Login</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
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
        
        /* Styles for the error modal */
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
        
        .error-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #ffebee;
            margin: 0 auto 20px;
        }
        
        .error-x {
            color: #f44336;
            font-size: 36px;
        }
        
        .modal-title {
            font-size: 24px;
            margin-bottom: 10px;
            color: #555;
        }
        
        .modal-message {
            font-size: 18px;
            color: #555;
            margin-bottom: 20px;
        }
        
        .ok-btn {
            background-color: #F5EEE6;
            color: #F5EEE6;
            border: none;
            padding: 10px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .ok-btn:hover {
            background-color: #6a5acd;
        }
    </style>
    </style>
</head>
<body>

    <div class="main-container">
        <?php include '../components/admin_header.php'; ?>
    </div>
    <section class="dashboard">
        <div class="heading">
            <h1>dashboard</h1>
            <img src="../image/logo2.jpeng">
        </div>
    </section>
    <script src="../js/admin_script.js"></script>
<script>
    document.getElementById('email').addEventListener('blur', function() {
        document.getElementById('email-validation').classList.toggle('show', this.value === '');
    });

    function closeModal() {
        document.getElementById('errorModal').style.display = 'none';
    }

    <?php if ($login_status === 'success') : ?>
        Swal.fire({
            icon: 'success',
            title: '<?= $login_message ?>',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            window.location.href = 'dashboard.php';
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
<? include '../components/alert.php'; ?>
</body>
</html>
