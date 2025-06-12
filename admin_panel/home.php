<?php 
include '../components/connect.php';
?>
<style type="text/css">
<?php include '../style0.css'; ?>
</style>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <title>Gelato Ice - home page</title>
</head>
<body>
    <?php include '../components/header.php'; ?>
    <div class="main">
        <?php include '../components/footer.php'; ?>
        <section class="home-section">
        <div class="slider">
            <div class="slider_slider slide1">
                <div class="overvlay"></div>
                <div class="slide-detail">
                <h1>Welcome To Gelato Ice</h1>
                <p>provides the best selection of Ice cream with distinctive flavors. Find your favorite here!</p>
                <a href="view1_product.php" class="btn" style="color: black;">Shop Now</a>
                </div>
                <div class="hero-dec-top"></div>
                <div class="hero-dec-bottom"></div>
            </div>
            <div class="slider_slider slide2">
    <div class="overvlay"></div>
    <div class="slide-detail" style="color: white;">
        <h1 style="color: white;">Welcome To Gelato Ice</h1>
        <p style="color: white;">provides the best selection of Ice cream with distinctive flavors. Find your favorite here!</p>
        <a href="view1_product.php" class="btn" style="color: black;">Shop Now</a>
    </div>
    <div class="hero-dec-top"></div>
    <div class="hero-dec-bottom"></div>
</div>

            <div class="slider_slider slide3">
                <div class="overvlay"></div>
                <div class="slide-detail" style="color: white;">
                <h1 style="color: white;">Welcome To Gelato Ice</h1>
                <p>provides the best selection of Ice cream with distinctive flavors. Find your favorite here!</p>
                <a href="view1_product.php" class="btn" style="color: black;">BShop Now</a>
                </div>
                <div class="hero-dec-top"></div>
                <div class="hero-dec-bottom"></div>
            </div>
            <div class="slider_slider slide4">
                <div class="overvlay"></div>
                <div class="slide-detail">
                <h1>Welcome To Gelato Ice</h1>
                <p>provides the best selection of Ice cream with distinctive flavors. Find your favorite here!</p>
                <a href="view1_product.php" class="btn" style="color: black;">Shop Now</a>
                </div>
                <div class="hero-dec-top"></div>
                <div class="hero-dec-bottom"></div>
            </div>
            <div class="slider_slider slide5">
                <div class="overvlay"></div>
                <div class="slide-detail" style="color: white">
                <h1 style="color: white;">Welcome To Gelato Ice</h1>
                <p>provides the best selection of Ice cream with distinctive flavors. Find your favorite here!</p>
                <a href="view1_product.php" class="btn" style="color: black";>Shop Now</a>
                </div>
                <div class="hero-dec-top"></div>
                <div class="hero-dec-bottom"></div>

        </section>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="script.js"></script>
    <?php include '../components/alert.php'; ?>
</body>
</html>