<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Username, email, and password are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'seller';
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, full_name, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $username, $email, $hashed_password, $role, $full_name, $phone, $address);
            
            if ($stmt->execute()) {
                $success = 'Seller account created successfully!';
                // Clear form
                $username = $email = $full_name = $phone = $address = '';
            } else {
                $error = 'Failed to create seller account. Please try again.';
            }
        }
        
        $stmt->close();
    }
}

$pageTitle = 'Create Seller Account';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6 text-lazada-black">Create Seller Account</h1>
    
    <?php if ($error): ?>
        <div class="alert-error border border-red-400 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert-success border border-green-400 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md p-8">
        <div class="alert-info border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-sm">
                <strong>Note:</strong> Only administrators can create seller accounts. This helps prevent fraudulent activities and ensures all sellers are verified.
            </p>
        </div>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Full Name *</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name ?? ''); ?>" required 
                       class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Username *</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required 
                       class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Email *</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required 
                       class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold mb-2 text-lazada-black">Password *</label>
                    <input type="password" name="password" required minlength="6"
                           class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block font-semibold mb-2 text-lazada-black">Confirm Password *</label>
                    <input type="password" name="confirm_password" required minlength="6"
                           class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
                </div>
            </div>
            
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" 
                       class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Address</label>
                <textarea name="address" rows="3" 
                          class="w-full input-lazada focus:ring-2 focus:ring-orange-500"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
            </div>
            
            <div class="flex gap-4">
                <button type="submit" class="btn-lazada flex-1">
                    Create Seller Account
                </button>
                <a href="<?php echo url('admin/users.php'); ?>" class="btn-lazada-secondary flex-1 text-center">
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

