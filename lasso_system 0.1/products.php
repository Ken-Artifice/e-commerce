<?php
require_once 'config/database.php';
require_once 'config/paths.php';
require_once 'config/auth.php';
require_once 'config/categories.php';

$conn = getDBConnection();

// Filter parameters
$category_id = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT p.*, u.full_name as seller_name, c.name as category_name 
          FROM products p 
          LEFT JOIN users u ON p.seller_id = u.id 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active'";

$params = [];
$types = '';

if ($category_id) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products_result = $stmt->get_result();

$pageTitle = 'Products';
include 'includes/header.php';
?>

<div class="container mx-auto px-4" style="padding-top: 32px; padding-bottom: 32px;">
    <h1 class="text-3xl font-bold mb-6 text-lazada-black">Products</h1>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <input type="text" name="search" placeholder="Search products..." 
                   value="<?php echo htmlspecialchars($search); ?>"
                   class="flex-1 input-lazada focus:ring-2 focus:ring-orange-500">
            
            <select name="category" class="input-lazada focus:ring-2 focus:ring-orange-500">
                <?php 
                $category_id = intval($category_id);
                echo renderGroupedCategoryOptions($conn, $category_id, true);
                ?>
            </select>
            
            <button type="submit" class="btn-lazada">
                Filter
            </button>
            
            <?php if ($search || $category_id): ?>
                <a href="<?php echo url('products.php'); ?>" class="btn-lazada-secondary">
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Products Grid -->
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
                        <span class="text-xs text-lazada-gray"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                        <h3 class="font-semibold text-lg mb-2 text-lazada-black"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-sm mb-2 text-lazada-gray">by <?php echo htmlspecialchars($product['seller_name']); ?></p>
                        <p class="font-bold text-xl mb-2 price-lazada">₱<?php echo number_format($product['price'], 2); ?></p>
                        <?php if ($product['stock'] > 0): ?>
                            <span class="text-sm stock-in">In Stock</span>
                        <?php else: ?>
                            <span class="text-sm stock-out">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
    
    <?php if ($products_result->num_rows === 0): ?>
        <div class="text-center py-12">
            <p class="text-xl text-lazada-gray">No products found.</p>
        </div>
    <?php endif; ?>
</div>

<?php 
$stmt->close();
$conn->close();
include 'includes/footer.php'; 
?>

