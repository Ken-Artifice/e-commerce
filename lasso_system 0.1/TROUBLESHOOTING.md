# Troubleshooting Guide - Admin Access Issues

## Common Issues and Solutions

### 1. Cannot Access Admin Dashboard

**Problem**: Getting "Not Found" error or redirected to login page

**Solutions**:

#### Check if Admin User Exists
1. Run the check script: `http://localhost/clone_system/check_admin.php`
2. This will verify if the admin user exists and create it if needed

#### Verify Admin Credentials
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@lasso.cs`

#### Manual Database Check
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database: `lazada_clone`
3. Go to `users` table
4. Check if there's a user with `role = 'admin'`

#### Create Admin User Manually (if needed)
Run this SQL in phpMyAdmin:
```sql
INSERT INTO users (username, email, password, role, full_name) 
VALUES ('admin', 'admin@lazada.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin User');
```

### 2. Login Redirects to Wrong Page

**Problem**: After login, admin is redirected to home page instead of admin dashboard

**Solution**: 
- Clear browser cookies/session
- Logout and login again
- Check that your user role is actually 'admin' in the database

### 3. Session Issues

**Problem**: Keeps getting logged out or redirected

**Solutions**:
1. Clear browser cache and cookies
2. Make sure cookies are enabled
3. Check PHP session configuration in `php.ini`

### 4. Path Issues

**Problem**: "Not Found" errors on admin pages

**Solution**: 
- Make sure you're accessing: `http://localhost/clone_system/admin/dashboard.php`
- All paths should now be relative and work correctly

### 5. Permission Denied

**Problem**: Can login but can't access admin pages

**Check**:
1. Verify your user role in database: `SELECT * FROM users WHERE username = 'admin'`
2. The role should be exactly `'admin'` (lowercase)
3. Try logging out and back in

## Testing Steps

1. **Check Database**:
   ```sql
   SELECT id, username, email, role FROM users WHERE role = 'admin';
   ```

2. **Test Login**:
   - Go to: `http://localhost/clone_system/login.php`
   - Use: username `admin`, password `admin123`
   - Should redirect to: `http://localhost/clone_system/admin/dashboard.php`

3. **Check Session**:
   - After login, check if session variables are set
   - Should have: `$_SESSION['user_role'] = 'admin'`

## Still Having Issues?

1. Check Apache error logs: `C:\xampp\apache\logs\error.log`
2. Check PHP error logs
3. Enable error display in PHP (for debugging):
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

## Quick Fix Script

Run `check_admin.php` in your browser:
- URL: `http://localhost/clone_system/check_admin.php`
- This will check and create admin user if needed

