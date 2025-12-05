<?php
// Include paths configuration if not already included
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../config/paths.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Lasso.cs - Shop products and book services">
    <title><?php echo $pageTitle ?? 'Lasso.cs'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=<?php echo time(); ?>">
    <link rel="manifest" href="<?php echo url('manifest.json'); ?>">
    <meta name="theme-color" content="#FF6A00">
    <link rel="apple-touch-icon" href="<?php echo url('assets/icon-192.png'); ?>">
    <style>
        /* Critical Lasso Color Palette - Inline to ensure it loads */
        :root {
            --lazada-orange: #FF6A00 !important;
            --lazada-orange-dark: #E55A00 !important;
            --lazada-orange-light: #FF8533 !important;
            --lazada-red: #E31E24 !important;
            --lazada-black: #000000 !important;
            --lazada-gray: #666666 !important;
        }
        
        /* Force navigation orange */
        .nav-lazada,
        nav.nav-lazada,
        nav[class*="nav-lazada"] {
            background-color: #FF6A00 !important;
            background: #FF6A00 !important;
        }
        
        /* Force button colors */
        .btn-lazada,
        button.btn-lazada,
        a.btn-lazada {
            background-color: #FF6A00 !important;
            background: #FF6A00 !important;
            color: #ffffff !important;
        }
        
        .btn-lazada:hover {
            background-color: #E55A00 !important;
            background: #E55A00 !important;
        }
        
        /* Force secondary button hover - more specific selectors */
        .btn-lazada-secondary:hover,
        a.btn-lazada-secondary:hover,
        button.btn-lazada-secondary:hover,
        nav .btn-lazada-secondary:hover,
        .register-btn-nav:hover {
            background-color: #FF6A00 !important;
            background: #FF6A00 !important;
            color: #ffffff !important;
            border-color: #FF6A00 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 2px 8px rgba(255, 106, 0, 0.3) !important;
        }
        
        /* Register button specific - prevent white revert */
        .register-btn-nav {
            background-color: #ffffff !important;
            color: #FF6A00 !important;
            border-color: #FF6A00 !important;
        }
        
        .register-btn-nav:hover {
            background-color: #FF6A00 !important;
            background: #FF6A00 !important;
            color: #ffffff !important;
            border-color: #FF6A00 !important;
        }
        
        /* Hero services button - use Lasso orange */
        .hero-services-btn {
            background-color: #FF8533 !important;
            background: #FF8533 !important;
            color: #ffffff !important;
            border-color: #FF8533 !important;
        }
        
        .hero-services-btn:hover {
            background-color: #FF6A00 !important;
            background: #FF6A00 !important;
            color: #ffffff !important;
            border-color: #FF6A00 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(255, 106, 0, 0.4) !important;
        }
        
        /* Hero button secondary hover */
        .hero-button-secondary:hover,
        .hero-lazada .btn-lazada-secondary:hover,
        .hero-lazada .hero-button-secondary:hover {
            background-color: rgba(255, 255, 255, 0.3) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
        }
        
        .btn-lazada-secondary:active {
            transform: translateY(0) !important;
        }
        
        /* Fix spacing for buttons in navigation */
        nav .btn-lazada-secondary,
        nav .register-btn-nav {
            margin-left: 12px !important;
        }
        
        /* Force hero section */
        .hero-lazada {
            background: linear-gradient(135deg, #FF6A00 0%, #FF8533 100%) !important;
            background-color: #FF6A00 !important;
        }
        
        /* Force price colors */
        .price-lazada {
            color: #FF6A00 !important;
        }
        
        /* Override Tailwind orange classes */
        .bg-orange-500,
        .bg-orange-600 {
            background-color: #FF6A00 !important;
        }
        
        .text-orange-500,
        .text-orange-600 {
            color: #FF6A00 !important;
        }
    </style>
    <script>
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?php echo url('sw.js'); ?>')
                    .then((registration) => {
                        console.log('SW registered: ', registration);
                    })
                    .catch((registrationError) => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    </script>
</head>
<body class="bg-white text-lazada-black">
    <div class="main-content">
    <nav class="shadow-lg nav-lazada" style="background-color: #FF6A00 !important; background: #FF6A00 !important;">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <a href="<?php echo url('index.php'); ?>" class="text-2xl font-bold text-white">Lasso.cs</a>
                
                <div class="hidden md:flex items-center space-x-4">
                    <a href="<?php echo url('index.php'); ?>" class="text-white nav-link px-3 py-2 rounded">Home</a>
                    <a href="<?php echo url('products.php'); ?>" class="text-white nav-link px-3 py-2 rounded">Products</a>
                    <a href="<?php echo url('services.php'); ?>" class="text-white nav-link px-3 py-2 rounded">Services</a>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('buyer')): ?>
                            <a href="<?php echo url('cart.php'); ?>" class="text-white nav-link px-3 py-2 rounded">Cart</a>
                            <a href="<?php echo url('orders.php'); ?>" class="text-white nav-link px-3 py-2 rounded">My Orders</a>
                            <a href="<?php echo url('bookings.php'); ?>" class="text-white nav-link px-3 py-2 rounded">My Bookings</a>
                        <?php elseif (hasRole('seller')): ?>
                            <a href="<?php echo url('seller/dashboard.php'); ?>" class="text-white nav-link px-3 py-2 rounded">Dashboard</a>
                        <?php elseif (hasRole('admin')): ?>
                            <a href="<?php echo url('admin/dashboard.php'); ?>" class="text-white nav-link px-3 py-2 rounded">Admin</a>
                        <?php endif; ?>
                        <a href="<?php echo url('profile.php'); ?>" class="text-white nav-link px-3 py-2 rounded">Profile</a>
                        <a href="<?php echo url('logout.php'); ?>" class="text-white nav-link px-3 py-2 rounded">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo url('login.php'); ?>" class="text-white nav-link px-3 py-2 rounded">Login</a>
                        <a href="<?php echo url('register.php'); ?>" class="btn-lazada-secondary register-btn-nav" 
                           style="background-color: #ffffff !important; color: #FF6A00 !important; border: 2px solid #FF6A00 !important; padding: 12px 24px !important; border-radius: 6px !important; font-weight: 600 !important; text-transform: uppercase !important; text-decoration: none !important; display: inline-block !important; transition: all 0.3s ease !important; margin-left: 12px !important; white-space: nowrap !important;">
                            Register
                        </a>
                    <?php endif; ?>
                </div>
                
                <button class="md:hidden" id="mobile-menu-btn">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
            
            <div class="mobile-menu md:hidden pb-4" id="mobile-menu">
                <a href="<?php echo url('index.php'); ?>" class="block px-3 py-2 text-white hover:opacity-80 rounded transition">Home</a>
                <a href="<?php echo url('products.php'); ?>" class="block px-3 py-2 text-white hover:opacity-80 rounded transition">Products</a>
                <a href="<?php echo url('services.php'); ?>" class="block px-3 py-2 text-white hover:opacity-80 rounded transition">Services</a>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (hasRole('buyer')): ?>
                        <a href="<?php echo url('cart.php'); ?>" class="block px-3 py-2 text-white hover:opacity-80 rounded transition">Cart</a>
                        <a href="<?php echo url('orders.php'); ?>" class="block px-3 py-2 text-white hover:opacity-80 rounded transition">My Orders</a>
                        <a href="<?php echo url('bookings.php'); ?>" class="block px-3 py-2 text-white hover:opacity-80 rounded transition">My Bookings</a>
                    <?php elseif (hasRole('seller')): ?>
                        <a href="<?php echo url('seller/dashboard.php'); ?>" class="block px-3 py-2 text-white hover:opacity-80 rounded transition">Dashboard</a>
                    <?php elseif (hasRole('admin')): ?>
                        <a href="<?php echo url('admin/dashboard.php'); ?>" class="block px-3 py-2 text-white hover:opacity-80 rounded transition">Admin</a>
                    <?php endif; ?>
                    <a href="<?php echo url('profile.php'); ?>" class="block px-3 py-2 text-white hover:opacity-80 rounded transition">Profile</a>
                    <a href="<?php echo url('logout.php'); ?>" class="block px-3 py-2 text-white hover:opacity-80 rounded transition">Logout</a>
                <?php else: ?>
                    <a href="<?php echo url('login.php'); ?>" class="block px-3 py-2 text-white hover:opacity-80 rounded transition">Login</a>
                    <a href="<?php echo url('register.php'); ?>" class="block px-3 py-2 text-white hover:opacity-80 rounded transition">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <!-- Main content area starts here - pages should place content after this comment -->

