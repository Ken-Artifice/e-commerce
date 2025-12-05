<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();

$success = '';
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $cat_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $cat_id);
    $stmt->execute();
    $stmt->close();
    require_once '../config/paths.php';
    header('Location: ' . url('admin/categories.php'));
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    
    if (empty($name)) {
        $error = 'Category name is required';
    } else {
        if ($category_id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $description, $category_id);
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $description);
        }
        
        if ($stmt->execute()) {
            $success = $category_id > 0 ? 'Category updated successfully' : 'Category added successfully';
            require_once '../config/paths.php';
            header('Location: ' . url('admin/categories.php'));
            exit();
        } else {
            $error = 'Failed to save category';
        }
        
        $stmt->close();
    }
}

// Get categories
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name");

$pageTitle = 'Manage Categories';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Manage Categories</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Add/Edit Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4">Add Category</h2>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="category_id" value="0">
                <div>
                    <label class="block font-semibold mb-2">Category Name *</label>
                    <input type="text" name="name" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block font-semibold mb-2">Description</label>
                    <textarea name="description" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"></textarea>
                </div>
                <button type="submit" class="w-full bg-orange-500 text-white py-3 rounded-lg font-semibold hover:bg-orange-600">
                    Add Category
                </button>
            </form>
        </div>
        
        <!-- Categories List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4">Categories</h2>
            <div class="space-y-2">
                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <div>
                            <p class="font-semibold"><?php echo htmlspecialchars($cat['name']); ?></p>
                            <?php if ($cat['description']): ?>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($cat['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        <a href="/admin/categories.php?delete=<?php echo $cat['id']; ?>" 
                           class="text-red-500 hover:underline text-sm"
                           onclick="return confirm('Delete this category?')">Delete</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>

