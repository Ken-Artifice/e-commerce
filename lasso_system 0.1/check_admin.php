<?php
// Quick script to check if admin user exists and create/fix if needed
require_once 'config/database.php';

$conn = getDBConnection();

// Check if admin exists
$result = $conn->query("SELECT * FROM users WHERE role = 'admin'");

if ($result->num_rows === 0) {
    // Create admin user with default credentials
    $username = 'admin';
    $email = 'admin@lazada.com';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $role = 'admin';
    $full_name = 'Admin User';
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, full_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $password, $role, $full_name);
    
    if ($stmt->execute()) {
        echo "<div style='padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 5px;'>";
        echo "<h2>Admin user created successfully!</h2>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><strong>Email:</strong> admin@lazada.com</p>";
        echo "</div>";
    } else {
        echo "<div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 5px;'>";
        echo "<h2>Error creating admin user</h2>";
        echo "<p>" . $conn->error . "</p>";
        echo "</div>";
    }
    $stmt->close();
} else {
    $admin = $result->fetch_assoc();
    echo "<div style='padding: 20px; background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; border-radius: 5px;'>";
    echo "<h2>Admin user already exists!</h2>";
    echo "<p><strong>Username:</strong> " . htmlspecialchars($admin['username']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</p>";
    echo "<p><strong>Role:</strong> " . htmlspecialchars($admin['role']) . "</p>";
    echo "<p><strong>Full Name:</strong> " . htmlspecialchars($admin['full_name'] ?? 'N/A') . "</p>";
    
    // Test password verification
    $test_password = 'admin123';
    if (password_verify($test_password, $admin['password'])) {
        echo "<p style='color: green;'><strong>✓ Password 'admin123' is correct!</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>✗ Password 'admin123' does NOT match!</strong></p>";
        echo "<p>Would you like to reset the password? <a href='?reset_password=1&user_id=" . $admin['id'] . "'>Reset to 'admin123'</a></p>";
    }
    echo "</div>";
}

// Handle password reset
if (isset($_GET['reset_password']) && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $new_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'admin'");
    $stmt->bind_param("si", $new_password, $user_id);
    
    if ($stmt->execute()) {
        echo "<div style='padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 5px; margin-top: 20px;'>";
        echo "<h2>Password reset successfully!</h2>";
        echo "<p><strong>New Password:</strong> admin123</p>";
        echo "<p>You can now login with username 'admin' and password 'admin123'</p>";
        echo "</div>";
    } else {
        echo "<div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 5px; margin-top: 20px;'>";
        echo "<p>Error resetting password: " . $conn->error . "</p>";
        echo "</div>";
    }
    $stmt->close();
}

$conn->close();
?>

