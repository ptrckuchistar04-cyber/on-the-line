<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine the base path for assets
$basePath = '';
$currentPath = $_SERVER['PHP_SELF'];
if (strpos($currentPath, '/admin/') !== false || strpos($currentPath, '/customer/') !== false) {
    $basePath = '../';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>On The Line - Real Estate & Vehicle Reservations</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="<?php echo $basePath; ?>index.php">
                    <span class="logo-text">ON THE LINE</span>
                    <span class="logo-tagline">Garage & Gate</span>
                </a>
            </div>
            
            <div class="nav-menu">
                <ul class="nav-links">
                    <li><a href="<?php echo $basePath; ?>index.php">Home</a></li>
                    <li><a href="<?php echo $basePath; ?>index.php?category=real_estate">Real Estate</a></li>
                    <li><a href="<?php echo $basePath; ?>index.php?category=vehicle">Vehicles</a></li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer'): ?>
                            <li>
                                <a href="<?php echo $basePath; ?>compare.php" class="compare-badge">
                                    Compare
                                    <span class="badge" style="display: none;">0</span>
                                </a>
                            </li>
                            <li><a href="<?php echo $basePath; ?>customer/cart.php">🛒 Cart</a></li>
                            <li><a href="<?php echo $basePath; ?>customer/orders.php">📋 My Orders</a></li>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'administrator'): ?>
                            <li><a href="<?php echo $basePath; ?>admin/dashboard.php">📊 Dashboard</a></li>
                            <li><a href="<?php echo $basePath; ?>admin/products.php">🏷️ Products</a></li>
                            <li><a href="<?php echo $basePath; ?>admin/orders.php">📦 Orders</a></li>
                        <?php endif; ?>
                        
                        <li class="nav-dropdown">
                            <a href="#" class="user-menu">
                                👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?> ▼
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo $basePath; ?>profile.php">My Profile</a></li>
                                <li><a href="<?php echo $basePath; ?>logout.php">Sign Out</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li><a href="<?php echo $basePath; ?>login.php" class="btn btn-outline">Login</a></li>
                        <li><a href="<?php echo $basePath; ?>register.php" class="btn btn-primary">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="main-content">