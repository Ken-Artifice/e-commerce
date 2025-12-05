<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('seller');

$user_id = getCurrentUserId();
$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $service_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $service_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . url('seller/services.php'));
    exit();
}

// Get services
$services_query = "SELECT s.*, c.name as category_name 
                  FROM services s 
                  LEFT JOIN categories c ON s.category_id = c.id 
                  WHERE s.seller_id = ? 
                  ORDER BY s.created_at DESC";
$stmt = $conn->prepare($services_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$services_result = $stmt->get_result();

$pageTitle = 'Manage Services';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Manage Services</h1>
        <a href="<?php echo url('seller/add_service.php'); ?>" class="bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600">
            Add Service
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($service = $services_result->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="w-16 h-16 bg-gray-200 rounded">
                                <?php if ($service['image']): ?>
                                    <img src="<?php echo htmlspecialchars($service['image']); ?>" alt="" class="w-full h-full object-cover rounded">
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($service['name']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($service['category_name'] ?? 'Uncategorized'); ?></td>
                        <td class="px-6 py-4">₱<?php echo number_format($service['price'], 2); ?></td>
                        <td class="px-6 py-4"><?php echo $service['duration']; ?> min</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs <?php echo $service['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($service['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                                     <a href="<?php echo url('seller/edit_service.php?id=' . $service['id']); ?>" class="text-blue-500 hover:underline mr-3">Edit</a>
                                     <a href="<?php echo url('seller/services.php?delete=' . $service['id']); ?>" 
                               class="text-red-500 hover:underline"
                               onclick="return confirm('Delete this service?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <?php if ($services_result->num_rows === 0): ?>
            <div class="text-center py-12">
                <p class="text-gray-500">No services yet. <a href="<?php echo url('seller/add_service.php'); ?>" class="text-orange-500 hover:underline">Add your first service</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
include '../includes/footer.php'; 
?>

