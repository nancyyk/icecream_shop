<style>
.logo img {
    width: 100px;
    height: auto;
    transition: filter 0.3s ease;
}
.logo:active img {
    filter: hue-rotate(290deg) saturate(1.5);
}
.flex {
    padding: 5px 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 30px;
}
.user-box {
    font-size: 12px;
    padding: 6px;
    background: #fff;
    box-shadow: 0 0 10px rgb(236, 233, 233);
    border-radius: 10px;
    max-width: 180px;
}
.buttons-container {
    display: flex;
    gap: 6px;
    margin-top: 3px;
    margin-bottom: 3px;
}

/* Tombol umum */
.btn {
    display: inline-block;
    text-decoration: none;
    padding: 3px 8px;
    border-radius: 5px;
    background:rgb(128, 57, 92); /* Pink */
    color: white; /* Putih agar kontras di box putih */
    font-size: 12px;
    font-weight: bold;
    transition: all 0.3s ease;
}
.btn:hover {
    background-color: #ff85c1; /* Pink lebih terang saat hover */
}
.btn:active {
    background-color: #333; /* Hitam saat ditekan */
    color: white;
}

body {
    background-color: pink;
    margin: 0;
    padding: 0;
    font-family: sans-serif;
    padding-top: 30px;
}

header {
    background-color: white;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    padding: 5px 0; /* diperkecil dari 10px */
    position: sticky;
    top: 0; /* bisa disesuaikan */
    z-index: 1000;
    margin-top: 30px;
}

#user-btn {
    font-size: 1.8rem; /* Sesuaikan ukuran ikon */
    cursor: pointer;
}

.cart-btn i {
    font-size: 1.8rem; /* Perbesar ikon keranjang */
}
.cart-btn sup {
    font-size: 1rem;
    vertical-align: top;
    color: white;
}
/* Assuming the white content area has a class or is the main element */
.content-container, main {
    padding-top: 30px;
    margin-top: 20px;
}
</style>
<header>
    <div class="flex">
        <a href="home.php" class="logo"><img src="../image/logo.jpeg"></a>

        <nav class="navbar">
            <a href="home.php">home</a>
            <a href="../admin_panel/view1_product.php">products</a>
            <a href="contact.php">contact us</a>
        </nav>


        <div class="user-box">
            <p>Login here: <span></span></p>
            <div class="buttons-container">
                <a href="login_universal.php" class="btn">login</a>
        </div>

    </div>
</header>