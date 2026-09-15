<?php
session_start();
$is_logged_in = isset($_SESSION['user']);
$user_id = $is_logged_in ? $_SESSION['user']['id'] : null;
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CUSAT Store</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
<div id="toast-container"></div>

<header class="main-header">
    <div class="header-container">
        
        <a href="index.php" class="logo-link">
            <img src="assets/cusat_logo_cropped.png" class="logo-img-file" alt="CUSAT Logo">
            <span class="logo-text">CUSAT Store</span>
        </a>

        <nav class="nav-menu">
            <a href="index.php" class="nav-item">Products</a>
            <a href="cart.php" class="nav-item">Cart</a>
            <?php if ($is_logged_in) { ?>
                <a href="orders.php" class="nav-item">My Orders</a>
            <?php } ?>
            <?php if ($is_admin) { ?>
                <a href="admin.php" class="nav-item">Admin Panel</a>
            <?php } ?>
        </nav>

        <div class="header-actions">
            <a href="cart.php" class="cart-status-link">
                <span class="cart-icon">🛒</span>
                <span id="cart-badge" class="badge-count" style="display: none;">0</span>
            </a>

            <?php if ($is_logged_in) { ?>
                <span class="user-greeting" style="margin-right: 10px; font-weight: 500; font-family: sans-serif; color: #333;">Hi, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
                <a href="logout.php" class="nav-btn btn-login" style="background-color: #e74c3c; border-color: #e74c3c; color: white;">Logout</a>
            <?php } else { ?>
                <a href="login.php" class="nav-btn btn-login">Login</a>
                <a href="register.php" class="nav-btn btn-register">Register</a>
            <?php } ?>
        </div>

    </div>
</header>