<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $service_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $stmt->close();
    require_once '../config/paths.php';
    header('Location: ' . url('admin/services.php'));
    exit();
}

// Get all services
$services_query = "SELECT s.*, u.full_name as seller_name, c.name as category_name 
                   FROM services s 
                   LEFT JOIN users u ON s.seller_id = u.id 
                   LEFT JOIN categories c ON s.category_id = c.id 
                   ORDER BY s.created_at DESC";
$services_result = $conn->query($services_query);

$pageTitle = 'All Services';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">All Services</h1>
    
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
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
                        <td class="px-6 py-4"><?php echo $service['id']; ?></td>
                        <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($service['name']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($service['seller_name'] ?? 'Unknown'); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($service['category_name'] ?? 'Uncategorized'); ?></td>
                        <td class="px-6 py-4">₱<?php echo number_format($service['price'], 2); ?></td>
                        <td class="px-6 py-4"><?php echo $service['duration']; ?> min</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs <?php echo $service['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($service['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="/admin/services.php?delete=<?php echo $service['id']; ?>" 
                               class="text-red-500 hover:underline"
                               onclick="return confirm('Delete this service?')">Delete</a>
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

