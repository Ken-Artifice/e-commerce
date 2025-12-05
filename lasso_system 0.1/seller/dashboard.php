<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('seller');

$user_id = getCurrentUserId();
$conn = getDBConnection();

// Get statistics
$products_count = $conn->query("SELECT COUNT(*) as count FROM products WHERE seller_id = $user_id")->fetch_assoc()['count'];
$services_count = $conn->query("SELECT COUNT(*) as count FROM services WHERE seller_id = $user_id")->fetch_assoc()['count'];
$bookings_count = $conn->query("SELECT COUNT(*) as count FROM bookings b JOIN services s ON b.service_id = s.id WHERE s.seller_id = $user_id")->fetch_assoc()['count'];

$pageTitle = 'Seller Dashboard';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Seller Dashboard</h1>
    
    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-600 mb-2">Products</h3>
            <p class="text-3xl font-bold text-orange-500"><?php echo $products_count; ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-600 mb-2">Services</h3>
            <p class="text-3xl font-bold text-orange-500"><?php echo $services_count; ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-600 mb-2">Bookings</h3>
            <p class="text-3xl font-bold text-orange-500"><?php echo $bookings_count; ?></p>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <a href="<?php echo url('seller/products.php'); ?>" class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition">
            <h2 class="text-2xl font-bold mb-2 text-gray-800">Manage Products</h2>
            <p class="text-gray-600">Add, edit, or remove products</p>
        </a>
        <a href="<?php echo url('seller/services.php'); ?>" class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition">
            <h2 class="text-2xl font-bold mb-2 text-gray-800">Manage Services</h2>
            <p class="text-gray-600">Add, edit, or remove services</p>
        </a>
        <a href="<?php echo url('seller/bookings.php'); ?>" class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition">
            <h2 class="text-2xl font-bold mb-2 text-gray-800">View Bookings</h2>
            <p class="text-gray-600">Manage service bookings</p>
        </a>
        <a href="<?php echo url('profile.php'); ?>" class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition">
            <h2 class="text-2xl font-bold mb-2 text-gray-800">Profile Settings</h2>
            <p class="text-gray-600">Update your profile information</p>
        </a>
    </div>
</div>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>

