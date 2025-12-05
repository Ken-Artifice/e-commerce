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
$query = "SELECT s.*, u.full_name as seller_name, c.name as category_name 
          FROM services s 
          LEFT JOIN users u ON s.seller_id = u.id 
          LEFT JOIN categories c ON s.category_id = c.id 
          WHERE s.status = 'active'";

$params = [];
$types = '';

if ($category_id) {
    $query .= " AND s.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

if ($search) {
    $query .= " AND (s.name LIKE ? OR s.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$query .= " ORDER BY s.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$services_result = $stmt->get_result();

$pageTitle = 'Services';
include 'includes/header.php';
?>

<div class="container mx-auto px-4" style="padding-top: 32px; padding-bottom: 32px;">
    <h1 class="text-3xl font-bold mb-6 text-lazada-black">Services</h1>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <input type="text" name="search" placeholder="Search services..." 
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
                <a href="<?php echo url('services.php'); ?>" class="btn-lazada-secondary">
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Services Grid -->
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
                        <span class="text-xs text-lazada-gray"><?php echo htmlspecialchars($service['category_name'] ?? 'Uncategorized'); ?></span>
                        <h3 class="font-semibold text-lg mb-2 text-lazada-black"><?php echo htmlspecialchars($service['name']); ?></h3>
                        <p class="text-sm mb-2 text-lazada-gray">by <?php echo htmlspecialchars($service['seller_name']); ?></p>
                        <p class="font-bold text-xl mb-2 price-lazada">₱<?php echo number_format($service['price'], 2); ?></p>
                        <p class="text-sm text-lazada-gray">Duration: <?php echo $service['duration']; ?> minutes</p>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
    
    <?php if ($services_result->num_rows === 0): ?>
        <div class="text-center py-12">
            <p class="text-xl text-lazada-gray">No services found.</p>
        </div>
    <?php endif; ?>
</div>

<?php 
$stmt->close();
$conn->close();
include 'includes/footer.php'; 
?>

