# Lasso.cs - E-commerce Platform with Service Booking

A simplified e-commerce platform built with PHP, CSS, JavaScript, and Tailwind CSS. Features product shopping and service booking functionality with role-based access (Buyer, Seller, Admin).

## Features

### User Roles
- **Buyer**: Browse products, book services, manage cart, place orders, view bookings
- **Seller**: Manage products and services, view bookings
- **Admin**: Manage users, categories, products, services, orders, and bookings

### Core Functionality
- User authentication and registration
- Product browsing and shopping cart
- Service booking system
- Order management
- Responsive design (mobile-friendly)
- Progressive Web App (PWA) support

## Installation

### Prerequisites
- XAMPP (or any PHP server with MySQL)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Clone or extract the project** to your XAMPP htdocs folder:
   ```
   C:\xampp\htdocs\clone_system
   ```

2. **Create the database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database/schema.sql` file
   - Or run the SQL commands manually

3. **Configure database connection** (if needed):
   - Edit `config/database.php` to match your MySQL credentials
   - Default: host=localhost, user=root, password='', database=lazada_clone

4. **Start Apache and MySQL** in XAMPP Control Panel

5. **Access the application**:
   - Open browser: http://localhost/clone_system/

## Default Admin Account

- **Username**: admin
- **Password**: admin123
- **Email**: admin@lasso.cs

## Project Structure

```
clone_system/
├── admin/              # Admin dashboard pages
├── assets/            # Images and static files
├── config/            # Configuration files
├── database/          # SQL schema file
├── includes/          # Header and footer templates
├── seller/            # Seller dashboard pages
├── index.php          # Home page
├── login.php          # Login page
├── register.php       # Registration page
├── products.php       # Product listing
├── services.php       # Service listing
├── cart.php           # Shopping cart
├── checkout.php       # Checkout page
├── orders.php         # User orders
├── bookings.php       # User bookings
├── profile.php         # User profile
├── manifest.json       # PWA manifest
├── sw.js              # Service worker
└── .htaccess          # Apache configuration
```

## PWA Features

The application is configured as a Progressive Web App:
- Installable on mobile devices
- Offline caching support
- Responsive design for mobile and desktop

## Usage

### For Buyers:
1. Register/Login as a buyer
2. Browse products and services
3. Add products to cart or book services
4. Complete checkout for products
5. View orders and bookings

### For Sellers:
1. Register/Login as a seller
2. Access seller dashboard
3. Add/edit products and services
4. Manage bookings

### For Admins:
1. Login with admin credentials
2. Access admin dashboard
3. Manage users, categories, products, services, orders, and bookings

## Technologies Used

- **PHP**: Server-side scripting
- **MySQL**: Database
- **Tailwind CSS**: Styling framework
- **JavaScript**: Client-side interactivity
- **PWA**: Progressive Web App features

## Notes

- Image URLs should be provided as full URLs (e.g., https://example.com/image.jpg)
- The application uses prepared statements to prevent SQL injection
- Password hashing uses PHP's password_hash() function
- Session-based authentication is implemented

## License

This is a simplified clone for educational purposes.

