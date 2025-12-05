<?php
require_once 'config/database.php';
require_once 'config/paths.php';
require_once 'config/auth.php';
requireLogin();

$user_id = getCurrentUserId();
$conn = getDBConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $user_id);
    
    if ($stmt->execute()) {
        $success = 'Profile updated successfully';
    } else {
        $error = 'Failed to update profile';
    }
    
    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$pageTitle = 'Profile';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">My Profile</h1>
    
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md p-8">
        <form method="POST" class="space-y-4">
            <div>
                <label class="block font-semibold mb-2">Username</label>
                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" disabled>
            </div>
            
            <div>
                <label class="block font-semibold mb-2">Role</label>
                <input type="text" value="<?php echo ucfirst($user['role']); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" disabled>
            </div>
            
            <div>
                <label class="block font-semibold mb-2">Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label class="block font-semibold mb-2">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label class="block font-semibold mb-2">Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label class="block font-semibold mb-2">Address</label>
                <textarea name="address" rows="4" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" class="w-full bg-orange-500 text-white py-3 rounded-lg font-semibold hover:bg-orange-600 transition">
                Update Profile
            </button>
        </form>
    </div>
</div>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>

