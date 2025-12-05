<?php
require_once 'config/database.php';
require_once 'config/paths.php';
require_once 'config/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'buyer';
    $full_name = trim($_POST['full_name'] ?? '');
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $conn = getDBConnection();
        
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, full_name) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $role, $full_name);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}

$pageTitle = 'Register';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 max-w-md" style="padding-top: 48px; padding-bottom: 48px;">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-3xl font-bold text-center mb-6 text-lazada-black">Create Account</h2>
        
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
        
        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Full Name</label>
                <input type="text" name="full_name" required 
                       class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Username</label>
                <input type="text" name="username" required 
                       class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Email</label>
                <input type="email" name="email" required 
                       class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
            </div>
            
            <input type="hidden" name="role" value="buyer">
            <div class="alert-info border border-blue-200 rounded-lg p-4">
                <p class="text-sm">
                    <strong>Note:</strong> Registration is only available for buyers. Seller accounts must be created by the administrator to prevent fraudulent activities.
                </p>
            </div>
            
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Password</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Confirm Password</label>
                <input type="password" name="confirm_password" required minlength="6"
                       class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
            </div>
            
            <button type="submit" class="btn-lazada w-full">
                Register
            </button>
        </form>
        
        <p class="text-center mt-6 text-lazada-black">
            Already have an account? <a href="<?php echo url('login.php'); ?>" class="link-lazada">Login here</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

