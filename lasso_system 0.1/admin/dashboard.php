<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();

// Get statistics
$users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$products_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$services_count = $conn->query("SELECT COUNT(*) as count FROM services")->fetch_assoc()['count'];
$orders_count = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$bookings_count = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];

$pageTitle = 'Admin Dashboard';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-lazada-black">Admin Dashboard</h1>
    
    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="dashboard-card">
            <h3 class="stat-label">Users</h3>
            <p class="stat-value"><?php echo $users_count; ?></p>
        </div>
        <div class="dashboard-card">
            <h3 class="stat-label">Products</h3>
            <p class="stat-value"><?php echo $products_count; ?></p>
        </div>
        <div class="dashboard-card">
            <h3 class="stat-label">Services</h3>
            <p class="stat-value"><?php echo $services_count; ?></p>
        </div>
        <div class="dashboard-card">
            <h3 class="stat-label">Orders</h3>
            <p class="stat-value"><?php echo $orders_count; ?></p>
        </div>
        <div class="dashboard-card">
            <h3 class="stat-label">Bookings</h3>
            <p class="stat-value"><?php echo $bookings_count; ?></p>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="<?php echo url('admin/sales_monitoring.php'); ?>" class="dashboard-card">
            <h2 class="text-2xl font-bold mb-2 text-lazada-black">Sales Monitoring</h2>
            <p class="text-lazada-gray">Real-time sales tracking and analytics</p>
        </a>
        <a href="<?php echo url('admin/sales_report.php'); ?>" class="dashboard-card">
            <h2 class="text-2xl font-bold mb-2 text-lazada-black">Sales Report</h2>
            <p class="text-lazada-gray">Generate detailed sales reports</p>
        </a>
        <a href="<?php echo url('admin/inventory_monitoring.php'); ?>" class="dashboard-card">
            <h2 class="text-2xl font-bold mb-2 text-lazada-black">Inventory Monitoring</h2>
            <p class="text-lazada-gray">Track product inventory and stock levels</p>
        </a>
        <a href="<?php echo url('admin/create_seller.php'); ?>" class="dashboard-card">
            <h2 class="text-2xl font-bold mb-2 text-lazada-black">Create Seller Account</h2>
            <p class="text-lazada-gray">Create new seller accounts (Admin only)</p>
        </a>
        <a href="<?php echo url('admin/users.php'); ?>" class="dashboard-card">
            <h2 class="text-2xl font-bold mb-2 text-lazada-black">Manage Users</h2>
            <p class="text-lazada-gray">View and manage all users</p>
        </a>
        <a href="<?php echo url('admin/categories.php'); ?>" class="dashboard-card">
            <h2 class="text-2xl font-bold mb-2 text-lazada-black">Manage Categories</h2>
            <p class="text-lazada-gray">Add or edit categories</p>
        </a>
        <a href="<?php echo url('admin/products.php'); ?>" class="dashboard-card">
            <h2 class="text-2xl font-bold mb-2 text-lazada-black">All Products</h2>
            <p class="text-lazada-gray">View all products</p>
        </a>
        <a href="<?php echo url('admin/services.php'); ?>" class="dashboard-card">
            <h2 class="text-2xl font-bold mb-2 text-lazada-black">All Services</h2>
            <p class="text-lazada-gray">View all services</p>
        </a>
        <a href="<?php echo url('admin/orders.php'); ?>" class="dashboard-card">
            <h2 class="text-2xl font-bold mb-2 text-lazada-black">All Orders</h2>
            <p class="text-lazada-gray">View and manage orders</p>
        </a>
        <a href="<?php echo url('admin/bookings.php'); ?>" class="dashboard-card">
            <h2 class="text-2xl font-bold mb-2 text-lazada-black">All Bookings</h2>
            <p class="text-lazada-gray">View and manage bookings</p>
        </a>
    </div>
</div>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>

