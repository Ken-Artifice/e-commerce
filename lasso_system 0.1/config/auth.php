<?php
// Include paths configuration
require_once __DIR__ . '/paths.php';

session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . url('login.php'));
        exit();
    }
}

// Require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ' . url('index.php'));
        exit();
    }
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user role
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}
?>

