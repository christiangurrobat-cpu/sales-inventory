# Fast Food Sales & Inventory System

A comprehensive Point of Sale (POS) and Inventory Management System built with PHP and MySQL for fast food restaurants.

## Features

- **Dashboard**: Real-time statistics and analytics
- **Point of Sale (POS)**: Quick order processing with cart management
- **Product Management**: Add, edit, and delete products with categories
- **Inventory Management**: Track stock levels and low stock alerts
- **Sales History**: View and manage all sales transactions
- **Reports & Analytics**: Generate sales reports and view top-selling products

## Requirements

- XAMPP (PHP 7.4+ and MySQL)
- Web browser (Chrome, Firefox, Edge, etc.)

## Installation

1. **Install XAMPP**
   - Download and install XAMPP from https://www.apachefriends.org/
   - Make sure Apache and MySQL services are running

2. **Setup Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the database schema:
     - Go to "Import" tab
     - Select `database/schema.sql` file
     - Click "Go" to import

3. **Copy Project Files**
   - Copy the entire project folder to `C:\xampp\htdocs\` (or your XAMPP htdocs directory)
   - Rename the folder to `fastfood-pos` (or any name you prefer)

4. **Access the Application**
   - Open your web browser
   - Navigate to: `http://localhost/fastfood-pos/`
   - The application should load and be ready to use

## Database Configuration

If you need to change database settings, edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password (default is empty)
define('DB_NAME', 'fastfood_inventory');
```

## Project Structure

```
fastfood-pos/
├── api/
│   ├── products.php      # Product CRUD operations
│   ├── categories.php    # Category management
│   ├── sales.php         # Sales/Order processing
│   └── reports.php       # Reports and analytics
├── assets/
│   ├── css/
│   │   └── style.css     # Main stylesheet
│   └── js/
│       └── app.js        # Main JavaScript application
├── config/
│   └── database.php      # Database configuration
├── database/
│   └── schema.sql        # Database schema and sample data
├── index.php             # Main application file
└── README.md            # This file
```

## Usage

### Dashboard
- View today's orders and revenue
- Check low stock alerts
- See top-selling products

### Point of Sale
- Click on products to add them to cart
- Adjust quantities using +/- buttons
- Apply discounts if needed
- Select payment method and complete order

### Products Management
- Add new products with details (name, price, cost, stock)
- Edit existing products
- Delete products (be careful!)

### Inventory Management
- View all products with current stock levels
- Low stock items are highlighted in red
- Adjust stock quantities as needed

### Sales History
- View all completed orders
- See order details including items and totals
- Filter by date range (coming soon)

### Reports
- Generate sales reports for specific date ranges
- View analytics and trends

## Sample Data

The database includes sample data:
- 5 categories (Burgers, Fries & Sides, Beverages, Desserts, Combo Meals)
- 10 sample products with prices and stock

## Security Notes

⚠️ **Important**: This is a basic system for local development. For production use, you should:
- Implement user authentication
- Add input validation and sanitization
- Use prepared statements (already implemented)
- Add CSRF protection
- Implement proper error handling
- Add access control and permissions

## Troubleshooting

**Database connection error:**
- Make sure MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Verify database `fastfood_inventory` exists

**Page not loading:**
- Ensure Apache is running in XAMPP
- Check file paths are correct
- Verify files are in the htdocs directory

**Products not showing:**
- Check if database was imported correctly
- Verify sample data exists in database
- Check browser console for JavaScript errors

## License

This project is open source and available for educational purposes.

## Support

For issues or questions, please check:
- XAMPP documentation
- PHP documentation
- MySQL documentation

---

**Enjoy managing your fast food restaurant! 🍔🍟🥤**


