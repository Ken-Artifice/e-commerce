<?php
require_once 'config/database.php';
require_once 'config/paths.php';
require_once 'config/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . url('index.php'));
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: ' . url('admin/dashboard.php'));
                } elseif ($user['role'] === 'seller') {
                    header('Location: ' . url('seller/dashboard.php'));
                } else {
                    header('Location: ' . url('index.php'));
                }
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
        
        $stmt->close();
        $conn->close();
    }
}

$pageTitle = 'Login';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 max-w-md" style="padding-top: 48px; padding-bottom: 48px;">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-3xl font-bold text-center mb-6 text-lazada-black">Login</h2>
        
        <?php if ($error): ?>
            <div class="alert-error border border-red-400 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Username or Email</label>
                <input type="text" name="username" required 
                       class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Password</label>
                <input type="password" name="password" required 
                       class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
            </div>
            
            <button type="submit" class="btn-lazada w-full">
                Login
            </button>
        </form>
        
        <p class="text-center mt-6 text-lazada-black">
            Don't have an account? <a href="<?php echo url('register.php'); ?>" class="link-lazada">Register here</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

