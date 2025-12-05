<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();
    require_once '../config/paths.php';
    header('Location: ' . url('admin/products.php'));
    exit();
}

// Get all products
$products_query = "SELECT p.*, u.full_name as seller_name, c.name as category_name 
                   FROM products p 
                   LEFT JOIN users u ON p.seller_id = u.id 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   ORDER BY p.created_at DESC";
$products_result = $conn->query($products_query);

$pageTitle = 'All Products';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">All Products</h1>
    
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
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
                        <td class="px-6 py-4"><?php echo $product['id']; ?></td>
                        <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($product['name']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($product['seller_name'] ?? 'Unknown'); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                        <td class="px-6 py-4">₱<?php echo number_format($product['price'], 2); ?></td>
                        <td class="px-6 py-4"><?php echo $product['stock']; ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs <?php echo $product['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($product['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="/admin/products.php?delete=<?php echo $product['id']; ?>" 
                               class="text-red-500 hover:underline"
                               onclick="return confirm('Delete this product?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>

