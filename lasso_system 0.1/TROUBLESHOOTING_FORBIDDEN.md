# Troubleshooting "Forbidden" Error

If you're getting a "403 Forbidden" error when accessing the application, try these solutions:

## Quick Fixes

### 1. Check Apache is Running
- Open XAMPP Control Panel
- Make sure Apache is running (green status)
- If not, click "Start" next to Apache

### 2. Check the URL
Make sure you're accessing:
- `http://localhost/clone_system/` (with trailing slash)
- OR `http://localhost/clone_system/index.php`

### 3. Check File Permissions (Windows)
On Windows, this is usually not an issue, but if you're on Linux/Mac:
```bash
chmod 755 clone_system
chmod 644 clone_system/*.php
```

### 4. Check .htaccess File
- Make sure `.htaccess` file exists in the root directory
- Check that it's not corrupted
- Try temporarily renaming it to `.htaccess.bak` to see if that's the issue

### 5. Check Apache Configuration
Open `C:\xampp\apache\conf\httpd.conf` and make sure:

```apache
# These should be uncommented (no # at the start)
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule headers_module modules/mod_headers.so

# AllowOverride should be set to All
<Directory "C:/xampp/htdocs">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

### 6. Restart Apache
After making changes:
1. Stop Apache in XAMPP Control Panel
2. Wait 5 seconds
3. Start Apache again

### 7. Check Error Logs
Check Apache error log:
- `C:\xampp\apache\logs\error.log`
- Look for specific error messages

### 8. Try Direct File Access
Try accessing files directly:
- `http://localhost/clone_system/index.php`
- `http://localhost/clone_system/login.php`

If these work but the directory doesn't, it's a DirectoryIndex issue.

### 9. Disable .htaccess Temporarily
1. Rename `.htaccess` to `.htaccess.backup`
2. Try accessing the site
3. If it works, the issue is in .htaccess
4. Rename it back and check the rules

### 10. Check Windows Firewall
- Make sure Windows Firewall isn't blocking Apache
- Add Apache to firewall exceptions if needed

## Common Causes

1. **DirectoryIndex not set** - Fixed in updated .htaccess
2. **Apache modules not loaded** - Check httpd.conf
3. **AllowOverride not set to All** - Check httpd.conf
4. **File permissions** - Usually not an issue on Windows
5. **Corrupted .htaccess** - Check file contents

## Still Not Working?

1. Check if other sites in htdocs work (e.g., `http://localhost/`)
2. Try accessing `http://localhost/clone_system/index.php` directly
3. Check Apache error logs for specific messages
4. Verify PHP is working: Create `test.php` with `<?php phpinfo(); ?>` and access it

