<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('seller');

$user_id = getCurrentUserId();
$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . url('seller/products.php'));
    exit();
}

// Get products
$products_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE p.seller_id = ? 
                   ORDER BY p.created_at DESC";
$stmt = $conn->prepare($products_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$products_result = $stmt->get_result();

$pageTitle = 'Manage Products';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Manage Products</h1>
        <a href="<?php echo url('seller/add_product.php'); ?>" class="bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600">
            Add Product
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="w-16 h-16 bg-gray-200 rounded">
                                <?php if ($product['image']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="" class="w-full h-full object-cover rounded">
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($product['name']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                        <td class="px-6 py-4">₱<?php echo number_format($product['price'], 2); ?></td>
                        <td class="px-6 py-4"><?php echo $product['stock']; ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs <?php echo $product['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($product['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                                     <a href="<?php echo url('seller/edit_product.php?id=' . $product['id']); ?>" class="text-blue-500 hover:underline mr-3">Edit</a>
                                     <a href="<?php echo url('seller/products.php?delete=' . $product['id']); ?>" 
                               class="text-red-500 hover:underline"
                               onclick="return confirm('Delete this product?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <?php if ($products_result->num_rows === 0): ?>
            <div class="text-center py-12">
                <p class="text-gray-500">No products yet. <a href="<?php echo url('seller/add_product.php'); ?>" class="text-orange-500 hover:underline">Add your first product</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
include '../includes/footer.php'; 
?>

