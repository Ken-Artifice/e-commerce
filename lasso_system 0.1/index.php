<?php
require_once 'config/database.php';
require_once 'config/paths.php';
require_once 'config/auth.php';

$conn = getDBConnection();

// Get featured products
$products_query = "SELECT p.*, u.full_name as seller_name FROM products p 
                   LEFT JOIN users u ON p.seller_id = u.id 
                   WHERE p.status = 'active' 
                   ORDER BY p.created_at DESC LIMIT 8";
$products_result = $conn->query($products_query);

// Get featured services
$services_query = "SELECT s.*, u.full_name as seller_name FROM services s 
                   LEFT JOIN users u ON s.seller_id = u.id 
                   WHERE s.status = 'active' 
                   ORDER BY s.created_at DESC LIMIT 6";
$services_result = $conn->query($services_query);

$pageTitle = 'Home';
include 'includes/header.php';
?>

<div class="container mx-auto px-4" style="padding-top: 32px; padding-bottom: 32px;">
    <!-- Hero Section -->
    <div class="rounded-lg shadow-lg p-8 md:p-12 hero-spacing text-white hero-lazada" style="background: linear-gradient(135deg, #FF6A00 0%, #FF8533 100%) !important; background-color: #FF6A00 !important;">
        <h1 class="text-4xl md:text-5xl font-bold mb-4 text-white">Welcome to Lasso.cs</h1>
        <p class="text-xl mb-6 text-white">Shop products and book services all in one place</p>
        <div class="flex flex-wrap gap-4" style="gap: 16px !important;">
            <a href="<?php echo url('products.php'); ?>" class="btn-lazada" style="background-color: #FF6A00 !important; color: #ffffff !important; padding: 14px 28px !important; border-radius: 6px !important; font-weight: 600 !important; text-transform: uppercase !important; text-decoration: none !important; display: inline-block !important; border: none !important; cursor: pointer !important; transition: all 0.3s ease !important;">
                Shop Now
            </a>
            <a href="<?php echo url('services.php'); ?>" class="btn-lazada hero-services-btn" 
               style="background-color: #FF8533 !important; color: #ffffff !important; border: 2px solid #FF8533 !important; padding: 14px 28px !important; border-radius: 6px !important; font-weight: 600 !important; text-transform: uppercase !important; text-decoration: none !important; display: inline-block !important; transition: all 0.3s ease !important; box-shadow: 0 2px 4px rgba(255, 133, 51, 0.3) !important;">
                Book Services
            </a>
        </div>
    </div>

    <!-- Featured Products -->
    <section class="section-spacing">
        <h2 class="text-3xl font-bold mb-6 text-lazada-black">Featured Products</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php while ($product = $products_result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-200 flex flex-col h-full">
                    <a href="<?php echo url('product_detail.php?id=' . $product['id']); ?>" class="flex flex-col h-full">
                        <div class="h-48 bg-gray-200 flex items-center justify-center flex-shrink-0">
                            <?php if ($product['image']): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="text-gray-400">No Image</span>
                            <?php endif; ?>
                        </div>
                        <div class="p-4 flex flex-col flex-grow">
                            <h3 class="font-semibold text-lg mb-2 text-lazada-black"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-sm mb-2 text-lazada-gray">by <?php echo htmlspecialchars($product['seller_name']); ?></p>
                            <p class="font-bold text-xl mb-2 price-lazada">₱<?php echo number_format($product['price'], 2); ?></p>
                            <?php if ($product['stock'] > 0): ?>
                                <span class="text-sm stock-in">In Stock (<?php echo $product['stock']; ?>)</span>
                            <?php else: ?>
                                <span class="text-sm stock-out">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-6">
            <a href="<?php echo url('products.php'); ?>" class="btn-lazada">View All Products →</a>
        </div>
    </section>

    <!-- Featured Services -->
    <section class="section-spacing">
        <h2 class="text-3xl font-bold mb-6 text-lazada-black">Featured Services</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($service = $services_result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-200 flex flex-col h-full">
                    <a href="<?php echo url('service_detail.php?id=' . $service['id']); ?>" class="flex flex-col h-full">
                        <div class="h-48 bg-gray-200 flex items-center justify-center flex-shrink-0">
                            <?php if ($service['image']): ?>
                                <img src="<?php echo htmlspecialchars($service['image']); ?>" alt="<?php echo htmlspecialchars($service['name']); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="text-gray-400">No Image</span>
                            <?php endif; ?>
                        </div>
                        <div class="p-4 flex flex-col flex-grow">
                            <h3 class="font-semibold text-lg mb-2 text-lazada-black"><?php echo htmlspecialchars($service['name']); ?></h3>
                            <p class="text-sm mb-2 text-lazada-gray">by <?php echo htmlspecialchars($service['seller_name']); ?></p>
                            <p class="font-bold text-xl mb-2 price-lazada">₱<?php echo number_format($service['price'], 2); ?></p>
                            <p class="text-sm text-lazada-gray">Duration: <?php echo $service['duration']; ?> minutes</p>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-6">
            <a href="<?php echo url('services.php'); ?>" class="btn-lazada">View All Services →</a>
        </div>
    </section>
</div>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>

