<?php
// Script to fix admin user password
require_once 'config/database.php';

$conn = getDBConnection();

// Find admin user
$result = $conn->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    
    // Reset password to admin123
    $new_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password, $admin['id']);
    
    if ($stmt->execute()) {
        echo "<!DOCTYPE html><html><head><title>Admin Password Reset</title></head><body>";
        echo "<div style='max-width: 600px; margin: 50px auto; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 5px;'>";
        echo "<h1>✓ Admin Password Reset Successfully!</h1>";
        echo "<p><strong>Username:</strong> " . htmlspecialchars($admin['username']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</p>";
        echo "<p><strong>New Password:</strong> admin123</p>";
        echo "<p><a href='login.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #FF6A00; color: white; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
        echo "</div></body></html>";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
} else {
    // Create admin if doesn't exist
    $username = 'admin';
    $email = 'admin@lazada.com';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $role = 'admin';
    $full_name = 'Admin User';
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, full_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $password, $role, $full_name);
    
    if ($stmt->execute()) {
        echo "<!DOCTYPE html><html><head><title>Admin Created</title></head><body>";
        echo "<div style='max-width: 600px; margin: 50px auto; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 5px;'>";
        echo "<h1>✓ Admin User Created!</h1>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><strong>Email:</strong> admin@lazada.com</p>";
        echo "<p><a href='login.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #FF6A00; color: white; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
        echo "</div></body></html>";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}

$conn->close();
?>

