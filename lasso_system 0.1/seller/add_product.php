<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
require_once '../config/categories.php';
requireRole('seller');

$user_id = getCurrentUserId();
$conn = getDBConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $image = trim($_POST['image'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name) || $price <= 0) {
        $error = 'Name and price are required';
    } else {
        $category_id = $category_id > 0 ? $category_id : null;
        $stmt = $conn->prepare("INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissdiss", $user_id, $category_id, $name, $description, $price, $stock, $image, $status);
        
        if ($stmt->execute()) {
            $success = 'Product added successfully';
            require_once '../config/paths.php';
            header('Location: ' . url('seller/products.php'));
            exit();
        } else {
            $error = 'Failed to add product';
        }
        
        $stmt->close();
    }
}

$pageTitle = 'Add Product';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Add Product</h1>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md p-8">
        <form method="POST" class="space-y-4">
            <div>
                <label class="block font-semibold mb-2">Product Name *</label>
                <input type="text" name="name" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label class="block font-semibold mb-2">Description</label>
                <textarea name="description" rows="4" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold mb-2">Price *</label>
                    <input type="number" name="price" step="0.01" min="0" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block font-semibold mb-2">Stock</label>
                    <input type="number" name="stock" min="0" value="0" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>
            </div>
            
            <div>
                <label class="block font-semibold mb-2">Category</label>
                <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                    <option value="0">Select Category</option>
                    <?php echo renderGroupedCategoryOptions($conn, 0, false); ?>
                </select>
            </div>
            
            <div>
                <label class="block font-semibold mb-2">Image URL</label>
                <input type="url" name="image" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                       placeholder="https://example.com/image.jpg">
            </div>
            
            <div>
                <label class="block font-semibold mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-orange-500 text-white py-3 rounded-lg font-semibold hover:bg-orange-600">
                    Add Product
                </button>
                <a href="<?php echo url('seller/products.php'); ?>" class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>

