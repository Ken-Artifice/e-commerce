<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();

// Inventory Statistics
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_stock = $conn->query("SELECT SUM(stock) as total FROM products")->fetch_assoc()['total'] ?? 0;
$low_stock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock < 10 AND stock > 0")->fetch_assoc()['count'];
$out_of_stock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock = 0")->fetch_assoc()['count'];
$total_value = $conn->query("SELECT SUM(stock * price) as total FROM products")->fetch_assoc()['total'] ?? 0;

// Products by category
$products_by_category = $conn->query("SELECT c.name as category, COUNT(p.id) as count, SUM(p.stock) as total_stock 
                                       FROM products p 
                                       LEFT JOIN categories c ON p.category_id = c.id 
                                       GROUP BY c.id, c.name 
                                       ORDER BY count DESC");

// Low stock products
$low_stock_products = $conn->query("SELECT p.*, u.full_name as seller_name, c.name as category_name 
                                     FROM products p 
                                     LEFT JOIN users u ON p.seller_id = u.id 
                                     LEFT JOIN categories c ON p.category_id = c.id 
                                     WHERE p.stock < 10 
                                     ORDER BY p.stock ASC, p.name ASC");

// Out of stock products
$out_of_stock_products = $conn->query("SELECT p.*, u.full_name as seller_name, c.name as category_name 
                                        FROM products p 
                                        LEFT JOIN users u ON p.seller_id = u.id 
                                        LEFT JOIN categories c ON p.category_id = c.id 
                                        WHERE p.stock = 0 
                                        ORDER BY p.name ASC");

$pageTitle = 'Inventory Monitoring';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-lazada-black">Inventory Monitoring</h1>
    
    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="dashboard-card">
            <h3 class="stat-label">Total Products</h3>
            <p class="stat-value"><?php echo $total_products; ?></p>
        </div>
        <div class="dashboard-card">
            <h3 class="stat-label">Total Stock</h3>
            <p class="stat-value"><?php echo number_format($total_stock); ?></p>
        </div>
        <div class="dashboard-card">
            <h3 class="stat-label">Inventory Value</h3>
            <p class="stat-value">₱<?php echo number_format($total_value, 2); ?></p>
        </div>
        <div class="dashboard-card">
            <h3 class="stat-label">Low Stock</h3>
            <p class="text-3xl font-bold text-lazada-yellow"><?php echo $low_stock; ?></p>
            <p class="text-sm text-lazada-gray">(< 10 units)</p>
        </div>
        <div class="dashboard-card">
            <h3 class="stat-label">Out of Stock</h3>
            <p class="text-3xl font-bold text-lazada-red"><?php echo $out_of_stock; ?></p>
        </div>
    </div>
    
    <!-- Products by Category -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4 text-lazada-black">Products by Category</h2>
        <div class="overflow-x-auto">
            <table class="table-lazada">
                <thead>
                    <tr>
                        <th class="text-left py-2 text-lazada-black">Category</th>
                        <th class="text-right py-2 text-lazada-black">Products</th>
                        <th class="text-right py-2 text-lazada-black">Total Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($cat = $products_by_category->fetch_assoc()): ?>
                        <tr>
                            <td class="py-2 text-lazada-black"><?php echo htmlspecialchars($cat['category'] ?? 'Uncategorized'); ?></td>
                            <td class="text-right py-2 text-lazada-black"><?php echo $cat['count']; ?></td>
                            <td class="text-right py-2 text-lazada-black"><?php echo number_format($cat['total_stock'] ?? 0); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Low Stock Products -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4" style="color: #000000;">Low Stock Products (< 10 units)</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2" style="color: #000000;">Product</th>
                        <th class="text-left py-2" style="color: #000000;">Seller</th>
                        <th class="text-left py-2" style="color: #000000;">Category</th>
                        <th class="text-right py-2" style="color: #000000;">Stock</th>
                        <th class="text-right py-2" style="color: #000000;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $low_stock_products->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="py-2" style="color: #000000;"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="py-2" style="color: #000000;"><?php echo htmlspecialchars($product['seller_name'] ?? 'Unknown'); ?></td>
                            <td class="py-2" style="color: #000000;"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                            <td class="text-right py-2" style="color: #ffc107; font-weight: bold;"><?php echo $product['stock']; ?></td>
                            <td class="text-right py-2" style="color: #000000;">₱<?php echo number_format($product['price'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Out of Stock Products -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4" style="color: #000000;">Out of Stock Products</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2" style="color: #000000;">Product</th>
                        <th class="text-left py-2" style="color: #000000;">Seller</th>
                        <th class="text-left py-2" style="color: #000000;">Category</th>
                        <th class="text-right py-2" style="color: #000000;">Price</th>
                        <th class="text-left py-2" style="color: #000000;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $out_of_stock_products->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="py-2" style="color: #000000;"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="py-2" style="color: #000000;"><?php echo htmlspecialchars($product['seller_name'] ?? 'Unknown'); ?></td>
                            <td class="py-2" style="color: #000000;"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                            <td class="text-right py-2" style="color: #000000;">₱<?php echo number_format($product['price'], 2); ?></td>
                            <td class="py-2">
                                <span class="px-2 py-1 rounded text-xs <?php echo $product['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo ucfirst($product['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>

