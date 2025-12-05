# Setup Guide - Lasso.cs

## Quick Start

### 1. Database Setup

1. Open **phpMyAdmin** (http://localhost/phpmyadmin)
2. Click on "Import" tab
3. Select the file: `database/schema.sql`
4. Click "Go" to import
5. The database `lazada_clone` will be created with all necessary tables (database name remains for compatibility)

**OR** manually run the SQL commands from `database/schema.sql`

### 2. Configure Database (if needed)

If your MySQL credentials are different, edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Change if different
define('DB_PASS', '');            // Change if you have a password
define('DB_NAME', 'lazada_clone');
```

### 3. Start XAMPP

1. Open **XAMPP Control Panel**
2. Start **Apache** and **MySQL** services

### 4. Access the Application

Open your browser and go to:
```
http://localhost/clone_system/
```

## Default Login Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@lazada.com`

## Testing the Application

### As a Buyer:
1. Register a new account (select "Buyer" role)
2. Browse products and services
3. Add products to cart
4. Book services
5. Complete checkout

### As a Seller:
1. Register a new account (select "Seller" role)
2. Login and access Seller Dashboard
3. Add products and services
4. Manage bookings

### As an Admin:
1. Login with admin credentials
2. Access Admin Dashboard
3. Manage users, categories, products, services, orders, and bookings

## PWA Features

The application is configured as a Progressive Web App:

1. **On Desktop**: Open in Chrome/Edge, click the install icon in the address bar
2. **On Mobile**: 
   - Open in Chrome/Safari
   - Use "Add to Home Screen" option
   - The app will work like a native app

## Troubleshooting

### Database Connection Error
- Make sure MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Verify database `lazada_clone` exists

### Page Not Found (404)
- Make sure you're accessing: `http://localhost/clone_system/`
- Check that Apache is running
- Verify `.htaccess` file exists

### Service Worker Not Working
- Make sure you're accessing via HTTP (not file://)
- Check browser console for errors
- Verify `sw.js` file is accessible

## File Structure

```
clone_system/
├── admin/          # Admin dashboard
├── assets/         # Images and icons (add PWA icons here)
├── config/         # Configuration files
├── database/       # SQL schema
├── includes/       # Header and footer templates
├── seller/         # Seller dashboard
├── index.php       # Home page
├── login.php       # Login page
├── register.php    # Registration
├── products.php    # Product listing
├── services.php    # Service listing
├── cart.php        # Shopping cart
├── checkout.php    # Checkout
├── orders.php      # User orders
├── bookings.php    # User bookings
├── profile.php     # User profile
├── manifest.json   # PWA manifest
├── sw.js          # Service worker
└── .htaccess      # Apache config
```

## Next Steps

1. Add PWA icons (192x192 and 512x512) to `assets/` folder
2. Customize colors and branding
3. Add product/service images
4. Configure email notifications (if needed)
5. Set up SSL for production deployment

## Support

For issues or questions, check the main README.md file.

