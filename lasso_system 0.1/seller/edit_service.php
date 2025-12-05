<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
require_once '../config/categories.php';
requireRole('seller');

$user_id = getCurrentUserId();
$conn = getDBConnection();

$service_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM services WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $service_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$service = $result->fetch_assoc();

if (!$service) {
    require_once '../config/paths.php';
    header('Location: ' . url('seller/services.php'));
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $duration = intval($_POST['duration'] ?? 60);
    $category_id = intval($_POST['category_id'] ?? 0);
    $image = trim($_POST['image'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name) || $price <= 0) {
        $error = 'Name and price are required';
    } else {
        $category_id = $category_id > 0 ? $category_id : null;
        $update_stmt = $conn->prepare("UPDATE services SET category_id = ?, name = ?, description = ?, price = ?, duration = ?, image = ?, status = ? WHERE id = ? AND seller_id = ?");
        $update_stmt->bind_param("issdissii", $category_id, $name, $description, $price, $duration, $image, $status, $service_id, $user_id);
        
        if ($update_stmt->execute()) {
            header('Location: ' . url('seller/services.php'));
            exit();
        } else {
            $error = 'Failed to update service';
        }
        
        $update_stmt->close();
    }
}

$pageTitle = 'Edit Service';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Edit Service</h1>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md p-8">
        <form method="POST" class="space-y-4">
            <div>
                <label class="block font-semibold mb-2">Service Name *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($service['name']); ?>" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label class="block font-semibold mb-2">Description</label>
                <textarea name="description" rows="4" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"><?php echo htmlspecialchars($service['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold mb-2">Price *</label>
                    <input type="number" name="price" step="0.01" min="0" value="<?php echo $service['price']; ?>" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block font-semibold mb-2">Duration (minutes)</label>
                    <input type="number" name="duration" min="1" value="<?php echo $service['duration']; ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>
            </div>
            
            <div>
                <label class="block font-semibold mb-2">Category</label>
                <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                    <option value="0">Select Category</option>
                    <?php 
                    $selected_category_id = intval($service['category_id'] ?? 0);
                    echo renderGroupedCategoryOptions($conn, $selected_category_id, false);
                    ?>
                </select>
            </div>
            
            <div>
                <label class="block font-semibold mb-2">Image URL</label>
                <input type="url" name="image" value="<?php echo htmlspecialchars($service['image'] ?? ''); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                       placeholder="https://example.com/image.jpg">
            </div>
            
            <div>
                <label class="block font-semibold mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                    <option value="active" <?php echo $service['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $service['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-orange-500 text-white py-3 rounded-lg font-semibold hover:bg-orange-600">
                    Update Service
                </button>
                <a href="<?php echo url('seller/services.php'); ?>" class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
include '../includes/footer.php'; 
?>

