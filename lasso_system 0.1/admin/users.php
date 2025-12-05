<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    if ($user_id != getCurrentUserId()) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
    require_once '../config/paths.php';
    header('Location: ' . url('admin/users.php'));
    exit();
}

// Get users
$users_result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

$pageTitle = 'Manage Users';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-lazada-black">Manage Users</h1>
        <a href="<?php echo url('admin/create_seller.php'); ?>" class="btn-lazada">
            Create Seller Account
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-lazada-gray">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-lazada-gray">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-lazada-gray">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-lazada-gray">Full Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-lazada-gray">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-lazada-gray">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-lazada-gray">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($user = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 text-lazada-black"><?php echo $user['id']; ?></td>
                        <td class="px-6 py-4 font-medium text-lazada-black"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td class="px-6 py-4 text-lazada-black"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-6 py-4 text-lazada-black"><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs 
                                <?php 
                                echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                    ($user['role'] === 'seller' ? 'bg-blue-100 text-blue-800' : 
                                    'bg-green-100 text-green-800'); 
                                ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-lazada-gray"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td class="px-6 py-4">
                            <?php if ($user['id'] != getCurrentUserId()): ?>
                                <a href="<?php echo url('admin/users.php?delete=' . $user['id']); ?>" 
                                   class="text-lazada-red"
                                   style="text-decoration: underline;"
                                   onclick="return confirm('Delete this user?')">Delete</a>
                            <?php else: ?>
                                <span class="text-lazada-gray">Current User</span>
                            <?php endif; ?>
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

